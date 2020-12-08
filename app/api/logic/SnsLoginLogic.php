<?php

namespace app\api\logic;

use app\api\logic\sns\
{DtSns, SnsAbstract, WxSns
};
use app\api\logic\traits\
{
    UserOp
};
use app\api\model\
{
    common\Img, log\UserLogs, rbac\UserOauths, rbac\User, rbac\UserAuth
};

use think\facade\
{Db, Request, Session
};

/**
 * 第三方授权登录
 */
class SnsLoginLogic extends BaseLogic
{
    use UserOp;

    private $_snsIns = null;

    public function __construct(string $sns = USEROAUTHS_TYPE_WEIXIN)
    {
        parent::__construct();

        //根据第三方授权的类型进行实例化
        switch ($sns)
        {
            case USEROAUTHS_TYPE_WEIXIN:
            {
                $this->_snsIns = new WxSns();
                break;
            }
            case USEROAUTHS_TYPE_DINGTALK:
                $this->_snsIns = new DtSns();
                break;
            default:
                E(\EC::PARAM_ERROR);
        }
    }

    // +--------------------------------------------------------------------------
    // |  JS-SDK
    // +--------------------------------------------------------------------------
    /**
     * JS-SDK sign 签名信息
     *
     * @param string $url
     *
     * @return array
     */
    public function JsSDKSign(string $url)
    {
        return WxSns::JsSDKSign($url);
    }

    // +--------------------------------------------------------------------------
    // |  授权
    // +--------------------------------------------------------------------------
    /**
     * 1.0 网页授权URL - snsapi_base (仅可以获取到粉丝的openid)
     *
     * @param string $authUrl
     * @param int    $isLogin
     * @param int    $isPic
     *
     * @return false|string|\think\Response
     */
    public function BaseRedirect(string $authUrl, int $isLogin = YES, int $isPic = YES)
    {
        //没有获取到粉丝的openid时，跳转到页面授权地址，进行登录/注册绑定
        $redirectURL = $this->_snsIns->getRedirectUrl($authUrl, $isLogin);
        if ($redirectURL === false)
        {
            static::$_error_code = \EC::DB_OPERATION_ERROR;
            static::$_error_msg  = 'getOauthRedirect 调用失败';

            return false;
        }

        // 返回数据
        $userName = null;
        if ((NO == $isLogin) && self::$_uname)
        {
            $userName = self::$_uname;
        }

        $sessionId = Session::getid();
        $this->_snsIns->CacheRmQrCode($sessionId);
        $this->_snsIns->CacheQrCode($sessionId, time(), SnsAbstract::WX_LOGIN_STATUE_QRCODE, '获取到二维码',
            null, null, null, $userName, $isLogin);
        if (YES == $isPic)
        {
            // 接口成功的处理
            if (ob_get_length() > 0)
                ob_end_clean();

            $filePath = get_qrfile($redirectURL);
            Img::RtnPic($filePath);

            $content = ob_get_clean();
            $content ? $code = 200 : $code = 304;

            return response($content, $code, ['Content-Length' => strlen($content)])
                ->contentType('image/png')->expires(1800);
        }

        return $redirectURL;
    }

    /**
     * 2.0 通过code换取网页授权access_token
     * 3.0 获取用户的openid
     *
     * @param string $key
     * @param int    $isLogin
     *
     * @return string
     */
    public function BaseInfo($key = '', $isLogin = YES)
    {
        do
        {
            //为了解决Android手机钉钉登录存在失败的问题 - 调用两次接口
            if($this->_snsIns->getType() == USEROAUTHS_TYPE_DINGTALK)
            {
                $qrCache = $this->_snsIns->CacheGetQrCode($key);
                if($qrCache && isset($qrCache['invokes']))
                {
                    $statue = $qrCache['statue'] ?? SnsAbstract::WX_LOGIN_STATUE_QRCODE;
                    $msg    = $qrCache['msg'] ?? '未知';

                    break;
                }
                $this->_snsIns->CacheQrCode($key, null, null, null, null, null,
                    null, null, null, null, 1);
            }

            $openId = $this->_snsIns->openId();
            if ($openId === false)
            {
                $statue = SnsAbstract::WX_LOGIN_STATUE_ACCESSTOKEN_FAILD;
                $msg    = '获取 AccessToken 失败';

                $this->_snsIns->CacheQrCode($key, null, $statue, $msg);
                break;
            }
            else
            {
                // 判断用户是否绑定
                $userName = UserOauths::instance()->CheckExist($openId);

                // 对于钉钉需要做特殊处理 - 钉钉导入平台后，自动绑定用户
                if (YES == $isLogin)
                {
                    $userName = $this->DingTalkAutoblind($openId, $userName);
                }

                //1.0 已经绑定
                if ($userName)
                {
                    // 判断用户是否删除
                    $user = User::instance();
                    $rst  = $user->CheckExist($userName);
                    if (!$rst) //用户被删除等原因
                    {
                        $statue = SnsAbstract::WX_LOGIN_STATUE_USER_REMOVE;
                        $msg    = '绑定的用户已删除，请联系管理员';

                        $this->_snsIns->CacheQrCode($key, null, $statue, $msg);
                        break;
                    }

                    //已经绑定的用户提示用户已经绑定
                    if (NO == $isLogin)
                    {
                        $statue = SnsAbstract::WX_LOGIN_STATUE_USER_EXIST;
                        $msg    = '此账号已与平台其他账号绑定';

                        $this->_snsIns->CacheQrCode($key, null, $statue, $msg);
                        break;
                    }

                    //用户登录相关操作处理
                    $token = $this->Login_Login($userName, true, $key);

                    $statue = SnsAbstract::WX_LOGIN_STATUE_LOGIN_SUCESS;
                    $msg    = '登录成功';

                    $headImgURL = $user->GetAvator($userName);
                    $fullName   = $user->GetFullName($userName);
                    $this->_snsIns->CacheQrCode($key, null, $statue, $msg, $token, $openId,
                        $headImgURL, $userName, null, $fullName);
                    break;
                }
                else//2.0 没有绑定
                {
                    //只有微信需要重新授权
                    if ($this->_snsIns->getType() != USEROAUTHS_TYPE_WEIXIN)
                    {
                        $fullName = null;
                        $uInfo    = $this->_snsIns->getUserInfo();
                        if ($uInfo && isset($uInfo['nick']))
                        {
                            $fullName = $uInfo['nick'];
                        }

                        $this->_snsIns->CacheQrCode($key, null, SnsAbstract::WX_LOGIN_STATUE_OAUTH_SUCCESS,
                            '等待登录/注册绑定', '', $openId, '', null, null,
                            $fullName);
                    }

                    $result = $this->_snsIns->getRedirectInfoUrl($key, Request::param('state'));

                    //重定向浏览器
                    //header("Location: {$result}");
                    return $result;
                }
            }
        } while (0);

        // 跳转的URL地址
        return $this->GetRedirctURL($statue, $msg, $key);

        //重定向浏览器
        //header("Location: {$oauthUrl}");
    }

    private function DingTalkAutoblind(string $openId, $userName)
    {
        if (!$userName && $this->_snsIns->getType() == USEROAUTHS_TYPE_DINGTALK)
        {
            //有userid时，返回userid
            if ($this->_snsIns->isUserIdType())
            {
                $userId = $this->_snsIns->userId();
                if ($userId)
                {
                    //获取userId对应的用户是否存在
                    $user = User::instance();
                    $rst  = $user->CheckExist($userId);

                    //绑定
                    if ($rst)
                    {
                        $userName = $userId;

                        $apiOauths = UserOauths::instance();
                        $apiOauths->Add($openId, $userName, $this->_snsIns->getType());

                        //操作日志
                        (new UserLogs())->Add($userName, USERLOGOP_OP_TYPE_ADD,
                            $this->_snsIns->getName() . '绑定', 'user_oauths', $openId);
                    }
                }
            }
        }

        return $userName;
    }

    /**
     * 2.0 通过code换取网页授权access_token
     * 3.0 获取授权后的用户资料
     *
     * @param string $key
     *
     * @return string
     */
    public function UserInfo($key = '')
    {
        do
        {
            $statue = SnsAbstract::WX_LOGIN_STATUE_OAUTH_SUCCESS;
            $msg    = '成功获取用户信息';

            //微信需要获取用户信息
            if ($this->_snsIns->getType() == USEROAUTHS_TYPE_WEIXIN)
            {
                $result = $this->_snsIns->getUserInfo();

                // 处理返回结果
                if ($result === false)
                {
                    $statue = SnsAbstract::WX_LOGIN_STATUE_ACCESSTOKEN_FAILD;
                    $msg    = 'getOauthUserinfo 用户资料获取失败';

                    $this->_snsIns->CacheQrCode($key, null, $statue, $msg);
                    break;
                }
                else
                {
                    $statue = SnsAbstract::WX_LOGIN_STATUE_OAUTH_SUCCESS;
                    $msg    = '成功获取用户信息';
                    $this->_snsIns->CacheQrCode($key, null, $statue, $msg, '', $result['openid'],
                        $result['headimgurl'] ?? '', null, null, $result['nickname'] ?? null);
                }
            }
            else
            {
                $this->_snsIns->CacheQrCode($key, null, $statue, $msg);
            }

            //判断是否需要进行【账号密码绑定或注册绑定】或添加绑定
            $qrcode = $this->_snsIns->CacheGetQrCode($key);
            if ($qrcode)
            {
                //自动注册新账户
                $userName = $qrcode['user_name'] ?? null;
                if ($qrcode['is_login'] == YES)
                {
                    $userName = $this->AutoRegister($qrcode['openid'],
                        $qrcode['headimgurl'] ?? null, $qrcode['full_name'] ?? null);
                }
                if (!$userName)
                {
                    return false;
                }

                //自动绑定
                $rtn = $this->BindLogin($userName, null, $key, false);
                if ($rtn)
                {
                    //说明直接绑定成功
                    $statue = SnsAbstract::WX_LOGIN_STATUE_BLIND_SUCESS;
                    $msg    = '用户绑定成功';
                }
            }

        } while (0);

        $oauthUrl = $this->GetRedirctURL($statue, $msg, $key);

        //重定向浏览器
        //header("Location: {$oauthUrl}");
        return $oauthUrl;
    }

    /**
     * 检测二维码
     */
    public function CheckQrCode()
    {
        $key = Session::getid();

        // 二维码不能为空
        $qrcode = $this->_snsIns->CacheGetQrCode($key);
        if (!$qrcode)
        {
            static::$_error_code = \EC::QRCODE_EXPIRE;

            return false;
        }

        // QrCode 过期 （30分钟）
        if (time() - $qrcode['create_time'] > SnsAbstract::CACHE_TIME)
        {
            $this->_snsIns->CacheRmQrCode($key);
            static::$_error_code = \EC::QRCODE_EXPIRE;

            return false;
        }

        return $qrcode;
    }

    private function AutoRegister(string $openId, string $imgURL = null, string $fullName = null)
    {
        if (!$imgURL)
        {
            $imgURL = '';
        }
        if (!$fullName)
        {
            $fullName = '新用户' . \PhpCrypt::Random_Pwd(6, true);
        }

        $userName = $this->_snsIns->getValidUserName($openId);
        $userInst = User::instance();
        do
        {
            if (!$userInst->CheckExist($userName))
            {
                break;
            }
            $userName = 'z' . \PhpCrypt::Random_Pwd(19);
        } while (true);

        $userName = strtolower($userName);
        $rtn      = UserLoginLogic::Register($userName, DEF_USER_PWD, null, null, $fullName, $imgURL);
        if (false === $rtn)
        {
            return false;
        }

        return $userName;
    }

    /**
     * 绑定登录
     *
     * @param string      $userName
     * @param string|null $pwd
     * @param string|null $key
     * @param bool        $bCheckPwd
     *
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function BindLogin(string $userName, string $pwd = null, string $key = null, bool $bCheckPwd = true)
    {
        //1.0 二维码不能为空
        $qrcode = $this->_snsIns->CacheGetQrCode($key);
        if (!$qrcode)
        {
            static::$_error_code = \EC::QRCODE_EXPIRE;

            return false;
        }

        // QrCode 过期 （30分钟）
        if (time() - $qrcode['create_time'] > SnsAbstract::CACHE_TIME)
        {
            $this->_snsIns->CacheRmQrCode($key);
            static::$_error_code = \EC::QRCODE_EXPIRE;

            return false;
        }

        if ($qrcode['statue'] != SnsAbstract::WX_LOGIN_STATUE_OAUTH_SUCCESS)
        {
            static::$_error_code = \EC::QRCODE_NOTBIND_ERROR;

            return false;
        }

        //2.0 判断用户是否绑定
        $apiOauths = UserOauths::instance();
        if ($apiOauths->CheckExistByName($userName, $this->_snsIns->getType()))
        {
            static::$_error_code = \EC::QRCODE_BIND_ERROR;

            return false;
        }

        // 判断用户是否删除
        $user = User::instance();
        $rst  = $user->CheckExist($userName);

        //用户被删除等原因
        if (!$rst)
        {
            static::$_error_code = \EC::USER_NOTEXIST_ERROR;

            return false;
        }

        //验证密码
        if ($bCheckPwd)
        {
            $loginInfo = UserAuth::instance()->GetInfo($userName);
            if (!$this->Check_Pwd($loginInfo['pwd'], $pwd))
            {
                static::$_error_code = \EC::USER_PASSWD_ERROR;

                return false;
            }
        }

        $openId = $qrcode['openid'];

        //修改用户图像
        Db::startTrans();
        try
        {
            //3.0 绑定
            $apiOauths = UserOauths::instance();
            $apiOauths->Add($openId, $userName, $this->_snsIns->getType());

            //操作日志
            (new UserLogs())->Add($userName, USERLOGOP_OP_TYPE_ADD, $this->_snsIns->getName() . '绑定',
                'user_oauths', $openId);

            $userPath = $user->GetAvator($userName);
            if (!$userPath && ($qrcode['headimgurl'] ?? ''))
            {
                $user->ModifyAvator($userName, $qrcode['headimgurl']);
            }

            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        //用户登录相关操作处理
        $token = '';
        if (YES == $qrcode['is_login'])
        {
            $token = $this->Login_Login($userName, true, $key);
        }

        //缓存登录成功信息
        $fullName = $user->GetFullName($userName);
        $this->_snsIns->CacheQrCode($key, null, SnsAbstract::WX_LOGIN_STATUE_LOGIN_SUCESS, '登录成功',
            $token, $openId, null, $userName, null, $fullName);

        return true;
    }

    /**
     * 是否绑定
     *
     * @param $user_name
     *
     * @return mixed
     */
    public function IsBind($user_name)
    {
        //2.0 判断用户是否绑定
        $apiOauths = UserOauths::instance();

        return $apiOauths->CheckExistByName($user_name, $this->_snsIns->getType());
    }

    /**
     * 移除绑定
     *
     * @param string $name
     */
    public function DelLogin(string $name)
    {
        //1.0 判断用户是否绑定
        $apiOauths = UserOauths::instance();
        $openId    = $apiOauths->CheckExistByName($name, $this->_snsIns->getType());
        if (!$openId)
        {
            static::$_error_code = \EC::QRCODE_NOTBIND_ERROR;

            return false;
        }

        //2.0 判断用户是否删除
        $user = User::instance();
        $rst  = $user->CheckExist($name);
        if (!$rst) //用户被删除等原因
        {
            static::$_error_code = \EC::USER_NOTEXIST_ERROR;

            return false;
        }

        //3.0 移除绑定
        $apiOauths = UserOauths::instance();
        $apiOauths->Del($openId);

        //操作日志
        $uL = UserLogs::instance();
        $uL->Add($name, USERLOGOP_OP_TYPE_REMOVE, $this->_snsIns->getName() . '解绑',
            'user_oauths', $openId);
    }

    private function GetRedirctURL($statue, $msg, $key)
    {
        // 跳转的URL地址
        $oauthUrl = Request::param('state');
        if (strpos($oauthUrl, '?') === false)
        {
            $oauthUrl .= '?statue=' . $statue . '&msg=' . $msg . '&key=' . $key . '&type=' . $this->_snsIns->getType();
        }
        else
        {
            $oauthUrl .= '&statue=' . $statue . '&msg=' . $msg . '&key=' . $key . '&type=' . $this->_snsIns->getType();
        }

        return $oauthUrl;
    }
}