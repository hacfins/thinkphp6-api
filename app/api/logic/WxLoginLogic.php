<?php

namespace app\api\logic;

use app\api\logic\traits\
{
    UserOp
};
use app\api\model\
{
    common\Img, log\UserLogs, rbac\UserOauths, rbac\User, rbac\UserAuth
};

use think\facade\
{Cache, Request, Session};

/**
 * 第三方授权 - 微信
 */
class WxLoginLogic extends BaseLogic
{
    use UserOp;

    private const CACHE_TIME = 1800; // 半小时

    // 状态
    private const WX_LOGIN_STATUE_QRCODE            = 1; // - 获取到二维码
    private const WX_LOGIN_STATUE_ACCESSTOKEN_FAILD = 2; // - 获取微信的AccessToken失败
    private const WX_LOGIN_STATUE_USER_REMOVE       = 3; // - 绑定的用户被删除
    private const WX_LOGIN_STATUE_LOGIN_SUCESS      = 4; // - 登录成功
    private const WX_LOGIN_STATUE_OAUTH_SUCCESS     = 5; // - 成功获取用户授权信息，等待登录/注册绑定
    private const WX_LOGIN_STATUE_USER_EXIST        = 6; // - 此微信账号已与平台其他账号绑定
    private const WX_LOGIN_STATUE_BLIND_SUCESS      = 7; // - 绑定成功

    // +--------------------------------------------------------------------------
    // |  JS-SDK
    // +--------------------------------------------------------------------------
    /**
     * JS-SDK sign 签名信息
     */
    public function JsSDKSign(string $url)
    {
        // 创建SDK实例
        $script = &load_wechat('Script');

        // 获取JsApi使用签名，通常这里只需要传 $url参数
        $options = $script->getJsSign($url);

        // 处理执行结果
        if ($options === false)
        {
            static::$_error_code = \EC::DB_OPERATION_ERROR;
            static::$_error_msg  = $script->errMsg;

            return false;
        }
        else
        {
            return $options;
        }
    }

    // +--------------------------------------------------------------------------
    // |  授权
    // +--------------------------------------------------------------------------
    /**
     * 1.0 网页授权URL - snsapi_base (仅可以获取到粉丝的openid)
     */
    public function BaseRedirect(string $auth_url, int $isLogin = YES, int $isPic = YES)
    {
        //没有获取到粉丝的openid时，跳转到页面授权地址，进行登录/注册绑定

        // SDK实例对象
        $oauth = &load_wechat('Oauth');

        $sessionId = Session::getid();

        $redirectUrl = Request::domain() . '/api/passport/wxlogin/baseinfo' . '?key=' . $sessionId . "&is_login=$isLogin";

        // Oauth 授权跳转接口
        $redirctURL = $oauth->getOauthRedirect($redirectUrl, $auth_url, 'snsapi_base');
        if ($redirctURL === false)
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
        $this->CacheRmQrCode($sessionId);
        $this->CacheQrCode($sessionId, time(), static::WX_LOGIN_STATUE_QRCODE, null, null, null, $userName, $isLogin);
        if (YES == $isPic)
        {
            // 接口成功的处理
            if (ob_get_length() > 0)
                ob_end_clean();

            $filePath = get_qrfile($redirctURL);
            Img::RtnPic($filePath);

            $content = ob_get_clean();
            $content ? $code = 200 : $code = 304;

            return response($content, $code, ['Content-Length' => strlen($content)])
                ->contentType('image/png')->expires(1800);
        }

        return $redirctURL;
    }

    /**
     * 2.0 通过code换取网页授权access_token
     * 3.0 获取用户的openid
     */
    public function BaseInfo($key = '', $isLogin = YES)
    {
        do
        {
            // SDK实例对象
            $oauth = &load_wechat('Oauth');

            // 尝试获取 2 次
            $cnt = 2;
            do
            {
                // 通过 code 获取 AccessToken 和 openid
                $result = $oauth->getOauthAccessToken();
                if ($result)
                {
                    break;
                }
            } while ((--$cnt) > 0);

            if ($result === false)
            {
                $statue = self::WX_LOGIN_STATUE_ACCESSTOKEN_FAILD;
                $msg    = '获取微信的 AccessToken 失败';

                $this->CacheQrCode($key, null, $statue);
                break;
            }
            else
            {
                $openId = $result['openid'];

                // 判断用户是否绑定
                $apiOauths = UserOauths::instance();
                $userName  = $apiOauths->CheckExist($openId);

                if ($userName)//1.0 已经绑定
                {
                    // 判断用户是否删除
                    $user = User::instance();
                    $rst  = $user->CheckExist($userName);
                    if (!$rst) //用户被删除等原因
                    {
                        $statue = self::WX_LOGIN_STATUE_USER_REMOVE;
                        $msg    = '绑定的用户已删除，请联系管理员';

                        $this->CacheQrCode($key, null, $statue);
                        break;
                    }

                    //已经绑定的用户提示用户已经绑定
                    if (NO == $isLogin)
                    {
                        $statue = self::WX_LOGIN_STATUE_USER_EXIST;
                        $msg    = '此微信账号已与平台其他账号绑定';

                        $this->CacheQrCode($key, null, $statue);
                        break;
                    }

                    //用户登录相关操作处理
                    $token = $this->Login_Login($userName, true, $key);

                    $statue = self::WX_LOGIN_STATUE_LOGIN_SUCESS;
                    $msg    = '登录成功';

                    $headImgURL = $user->GetAvator($userName);
                    $fullName   = $user->GetFullName($userName);
                    $this->CacheQrCode($key, null, $statue, $token, $openId, $headImgURL, $userName, null, $fullName);
                    break;
                }
                else//2.0 没有绑定
                {
                    $result = $oauth->getOauthRedirect(Request::domain() . '/api/passport/wxlogin/userinfo' . '?key=' . $key,
                        Request::param('state'), 'snsapi_userinfo');

                    //重定向浏览器
                    //header("Location: {$result}");
                    return $result;
                }
            }

        } while (0);

        // 跳转的URL地址
        $oauthUrl = $this->GetRedirctURL($statue, $msg, $key);

        //重定向浏览器
        //header("Location: {$oauthUrl}");
        return $oauthUrl;
    }

    /**
     * 2.0 通过code换取网页授权access_token
     * 3.0 获取授权后的用户资料
     */
    public function UserInfo($key = '')
    {
        do
        {
            // SDK实例对象
            $oauth = &load_wechat('Oauth');

            // 通过 code 获取 AccessToken 和 openid
            $result = $oauth->getOauthAccessToken();
            if ($result === false)
            {
                $statue = self::WX_LOGIN_STATUE_ACCESSTOKEN_FAILD;
                $msg    = '获取微信的 AccessToken 失败';

                $this->CacheQrCode($key, null, $statue);
                break;
            }
            else
            {
                // 获取授权后的用户资料
                $result = $oauth->getOauthUserinfo($result['access_token'], $result['openid']);

                // 处理返回结果
                if ($result === false)
                {
                    $statue = self::WX_LOGIN_STATUE_ACCESSTOKEN_FAILD;
                    $msg    = 'getOauthUserinfo 用户资料获取失败';

                    $this->CacheQrCode($key, null, $statue);
                    break;
                }
                else
                {
                    $statue = self::WX_LOGIN_STATUE_OAUTH_SUCCESS;
                    $msg    = '成功获取用户信息';
                    $this->CacheQrCode($key, null, $statue, '', $result['openid'], $result['headimgurl']);

                    //判断是否需要进行账号密码绑定或注册绑定
                    $qrcode = $this->CacheGetQrCode($key);
                    if ($qrcode && $qrcode['is_login'] == NO && $qrcode['user_name'])
                    {
                        $rtn = $this->BindLogin($qrcode['user_name'], null, $key, false);
                        if($rtn)
                        {
                            //说明直接绑定成功
                            $statue = self::WX_LOGIN_STATUE_BLIND_SUCESS;
                            $msg    = '用户绑定成功';
                        }
                    }
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
        $qrcode = $this->CacheGetQrCode($key);
        if (!$qrcode)
        {
            static::$_error_code = \EC::QRCODE_EXPIRE;

            return false;
        }

        // QrCode 过期 （30分钟）
        if (time() - $qrcode['create_time'] > self::CACHE_TIME)
        {
            $this->CacheRmQrCode($key);
            static::$_error_code = \EC::QRCODE_EXPIRE;

            return false;
        }

        return $qrcode;
    }

    /**
     * 绑定登录
     *
     * @param string $userName
     * @param string $pwd
     * @param string $key
     * @param bool   $bCheckPwd
     *
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function BindLogin(string $userName, string $pwd = null, string $key = null, bool $bCheckPwd = true)
    {
        //1.0 二维码不能为空
        $qrcode = $this->CacheGetQrCode($key);
        if (!$qrcode)
        {
            static::$_error_code = \EC::QRCODE_EXPIRE;

            return false;
        }

        // QrCode 过期 （30分钟）
        if (time() - $qrcode['create_time'] > self::CACHE_TIME)
        {
            $this->CacheRmQrCode($key);
            static::$_error_code = \EC::QRCODE_EXPIRE;

            return false;
        }

        if ($qrcode['statue'] != self::WX_LOGIN_STATUE_OAUTH_SUCCESS)
        {
            static::$_error_code = \EC::QRCODE_NOTBIND_ERROR;

            return false;
        }

        //2.0 判断用户是否绑定
        $apiOauths = UserOauths::instance();
        if ($apiOauths->CheckExistByName($userName))
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

        //3.0 绑定
        $apiOauths = UserOauths::instance();
        $apiOauths->Add($openId, $userName);

        //操作日志
        (new UserLogs())->Add($userName, USERLOGOP_OP_TYPE_ADD, '第三方帐号绑定', 'user_oauths', $openId);

        //修改用户图像
        try
        {
            $userPath = $user->GetAvator($userName);
            if (!isset($userPath) || empty($userPath))
            {
                $user->ModifyAvator($userName, $qrcode['headimgurl']);
            }
        }
        catch (\Throwable $e)
        {
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
        $this->CacheQrCode($key, null, self::WX_LOGIN_STATUE_LOGIN_SUCESS, $token, $openId, null, $userName,
            null, $fullName);

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

        return $apiOauths->CheckExistByName($user_name);
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
        $openId    = $apiOauths->CheckExistByName($name);
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
        $uL->Add($name, USERLOGOP_OP_TYPE_REMOVE, '第三方帐号解绑', 'user_oauths', $openId);
    }

    private function GetRedirctURL($statue, $msg, $key)
    {
        // 跳转的URL地址
        $oauthUrl = Request::param('state');
        if (strpos($oauthUrl, '?') === false)
        {
            $oauthUrl .= '?statue=' . $statue . '&msg=' . $msg . '&key=' . $key;
        }
        else
        {
            $oauthUrl .= '&statue=' . $statue . '&msg=' . $msg . '&key=' . $key;
        }

        return $oauthUrl;
    }

    // +--------------------------------------------------------------------------
    // |  Cache
    // +--------------------------------------------------------------------------
    private function CacheKey($key)
    {
        if (is_null($key))
            $key = CACHE_OAUTH_OPENID . Session::getid();
        else
            $key = CACHE_OAUTH_OPENID . $key;

        return $key;
    }

    /**
     * @param null   $key          缓存的key
     * @param null   $create_time  缓存创建时间
     * @param int    $statue       第三方登录的状态
     *
     * @param string $access_token 登录成功返回的Token
     */
    private function CacheQrCode(
        $key = null, $create_time = null, $statue = null, $access_token = null,
        $openid = null, string $headimgurl = null, string $userName = null,
        int $isLogin = null, string $fullName = '')
    {
        $key    = $this->CacheKey($key);
        $qrcode = Cache::get($key);
        if (!$qrcode)
        {
            $qrcode = [];
        }

        // 创建时间
        if (isset($create_time))
        {
            $qrcode['create_time'] = $create_time;
        }
        // 状态
        if (isset($statue))
        {
            $qrcode['statue'] = $statue;
        }
        // 用户名
        if (isset($userName))
        {
            $qrcode['user_name'] = $userName;
        }
        if (isset($fullName))
        {
            $qrcode['full_name'] = $fullName;
        }
        // 登录后的凭证
        if (isset($access_token))
        {
            $qrcode['sg'] = $access_token;
        }
        // 第三方唯一标识符
        if (isset($openid))
        {
            $qrcode['openid'] = $openid;
        }
        // 第三方用户头像
        if (isset($headimgurl))
        {
            $qrcode['headimgurl'] = $headimgurl;
        }
        // 是否是登录请求
        if (isset($isLogin))
        {
            $qrcode['is_login'] = $isLogin;
        }

        Cache::set($key, $qrcode, self::CACHE_TIME);
    }

    /**
     * 删除缓存
     *
     * @param null $key
     */
    private function CacheRmQrCode($key = null)
    {
        $key = $this->CacheKey($key);
        Cache::delete($key);
    }

    private function CacheGetQrCode($key = null)
    {
        $key = $this->CacheKey($key);

        return Cache::get($key);
    }
}