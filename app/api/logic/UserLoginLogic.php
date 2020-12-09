<?php

namespace app\api\logic;

use app\api\logic\traits\
{Captcha, UserOp};
use app\api\model\
{
    log\UserLogs, rbac\UserRoles, rbac\UserTokens, rbac\User, rbac\UserAuth
};
use Carbon\Carbon;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;

/**
 * 用户登录\注册\找回密码
 */
class UserLoginLogic extends BaseLogic
{
    use Captcha;
    use UserOp;

    //==================================================== 登录 =========================================================

    /**
     * 登录
     *
     * @param string $name 用户名 | 手机 | 邮箱
     * @param string $pwd  密码
     * @param int    $bFreelogin
     *
     * @return array | bool
     */
    public function Login(string $name, string $pwd, int $bFreelogin = YES)
    {
        try
        {
            //1.0 检测用户名
            $userName = $this->CheckName($name);
            if (!$userName)
            {
                return false;
            }

            //2.0 验证密码
            $loginInfo = (UserAuth::instance())->GetInfo($userName);
            if (!$loginInfo)
            {
                static::$_error_code = \EC::USER_NOTEXIST_ERROR;

                return false;
            }
            if (!$this->Check_Pwd($loginInfo['pwd'], $pwd))
            {
                static::$_error_code = \EC::USER_PASSWD_ERROR;

                return false;
            }

            //3.0 用户登录相关操作处理
            $token = $this->Login_Login($userName, $bFreelogin);

            return [
                'sg'   => $token,
                'name' => $userName
            ];
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    /**
     * 登录 - 校验码
     *
     * @param string $name 手机 | 邮箱
     * @param int    $bFreelogin
     *
     * @return array | bool
     */
    public function Login_Verify(string $name, int $bFreelogin = YES)
    {
        try
        {
            //1.0 检测用户名
            $userName = $this->CheckName($name);
            if (!$userName)
            {
                if (validate_phone($name))
                {
                    $fullName = '新用户' . substr($name,-4);
                    $userInst = User::instance();
                    do
                    {
                        $userName = 'a' . \PhpCrypt::Random_Pwd(15) . substr($name,-4);
                        if (!$userInst->CheckExist($userName))
                        {
                            break;
                        }
                    } while (true);

                    $userName = strtolower($userName);
                    $rtn      = $this->Register($userName, DEF_USER_PWD, null, $name, $fullName);
                    if (false === $rtn)
                    {
                        return false;
                    }

                    static::$_error_code = \EC::SUCCESS;
                }
                else
                {
                    return false;
                }
            }

            //2.0 用户登录相关操作处理
            $token = $this->Login_Login($userName, $bFreelogin);

            return [
                'sg'   => $token,
                'name' => $userName
            ];
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    /**
     * 退出
     *
     * @param string $userName 用户名
     */
    public function Logout(string $userName)
    {
        try
        {
            //1.0 检测用户名
            $userName = $this->CheckName($userName);
            if (!$userName)
            {
                return false;
            }

            //用户退出登录相关操作处理
            $this->Login_out(self::$_token);
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    /**
     * 通过客户端打开 URL
     *
     * @param string $name 用户名 | 手机 | 邮箱
     * @param string $sg   token
     *
     * @return array|bool
     */
    public function OpenUrl(string $name, string $sg)
    {
        try
        {
            //1.0 检测用户名
            $userName = $this->CheckName($name);
            if (!$userName)
            {
                return false;
            }

            //2.0 验证 token 是否有效
            $tokenUserInfo = $this->CheckToken($sg);
            if (!$tokenUserInfo)
            {
                return false;
            }
            if ($userName != $tokenUserInfo['user_name'])
            {
                static::$_error_code = \EC::PARAM_SAFEERROR;

                return false;
            }

            //3.0 检查是否在同台机器
            $userLogs = UserLogs::instance();
            $opId     = $userLogs->GetOpId('user_tokens', $sg);
            if ($opId)
            {
                $logInfo = $userLogs->GetInfo($opId);
                if ($logInfo && ($logInfo['ip'] != ip2long(Request::ip()) ))
                {
                    static::$_error_code = \EC::PARAM_SAFEERROR;

                    return false;
                }
            }

            //4.0 用户登录相关操作处理
            $token = $this->Login_Login($userName, NO);

            return [
                'sg'   => $token,
                'name' => $userName
            ];
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    //==================================================== 注册 =========================================================

    /**
     * 检查用户名是否存在
     *
     * @param string $name
     *
     * @return int
     */
    public function ExistName(string $name)
    {
        $user = User::instance();
        $rst  = $user->CheckExist($name) ? YES : NO;

        return $rst;
    }

    /**
     * 检查邮箱是否存在
     *
     * @param string $email
     * @param int    $except
     *
     * @return int
     */
    public function ExistEmail(string $email, $except = YES, string $name = null)
    {
        $userAuth = UserAuth::instance();

        $exceptName = null;
        if ($except == YES)
        {
            if($name)
            {
                $exceptName = $name;
            }
            else
            {
                $exceptName = self::$_uname ?? null;
            }
        }

        $rst = $userAuth->CheckExist_Email($email, $exceptName) ? YES : NO;

        return $rst;
    }

    /**
     * 检查手机号是否存在
     *
     * @param string $phone
     * @param int    $except
     *
     * @return int
     */
    public function ExistPhone(string $phone, $except = YES, string $name = null)
    {
        $userAuth = UserAuth::instance();

        $exceptName = null;
        if ($except == YES)
        {
            if ($name)
            {
                $exceptName = $name;
            }
            else
            {
                $exceptName = self::$_uname ?? null;
            }
        }

        $rst = $userAuth->CheckExist_Phone($phone, $exceptName) ? YES : NO;

        return $rst;
    }

    /**
     * 注册
     *
     * @param string      $name
     * @param string      $pwd
     *
     * @param string|null $email
     * @param string|null $phone
     * @param string|null $nickName
     * @param string      $avator
     *
     * @return false|string
     */
    public static function Register(string $name, string $pwd, string $email = null, string $phone = null,
        string $nickName = null, string $avator = '')
    {
        $user  = User::instance();
        $uAuth = UserAuth::instance();

        //1.0 检查用户名、邮箱是否存在
        if ($user->CheckExist($name))
        {
            static::$_error_code = \EC::USER_EXIST_ERROR;

            return false;
        }

        if (isset($email))
        {
            if ($uAuth->CheckExist_Email($email))
            {
                static::$_error_code = \EC::USER_EMAIL_EXIST_ERROR;

                return false;
            }
        }
        else
        {
            $email = '';
        }

        if (isset($phone))
        {
            if ($uAuth->CheckExist_Phone($phone))
            {
                static::$_error_code = \EC::USER_PHONE_EXIST_ERROR;

                return false;
            }
        }
        else
        {
            $phone = '';
        }

        if($name == $pwd)
        {
            static::$_error_code = \EC::USER_PASSWD_SAME_ERROR;
            return false;
        }

        Db::startTrans();
        try
        {
            //添加用户
            $user->Add($name, $nickName, '', USER_SEX_UNKOWN, $avator);
            UserAuth::instance()->Add($name, $pwd, $phone, $email);
            if ($name != USER_NAME_ADMIN)
            {
                //2.0 设置角色为普通用户
                (UserRoles::instance())->ModifyRoleByUser($name, [ROLE_USER_ROLE]);
            }

            //添加用户统计-日志
            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        //设置登录信息
        Session::clear();
        cookie_clear();

        //3.0 发送通知

        return true;
    }

    //==================================================== 找回密码 =====================================================

    /**
     * 检测邮箱是否有效
     *
     * @param string $email
     *
     */
    public function CheckEmail(string $email)
    {
        try
        {
            $address = strtolower($email);

            //1.0 获取用户信息
            $userAuth = UserAuth::instance();
            $userName = $userAuth->CheckExist_Email($address);
            if (!$userName)
            {
                static::$_error_code = \EC::USER_NOTEXIST_ERROR;

                return false;
            }

            $userInfo = User::instance()->GetInfo($userName);
            if ($userInfo['status'] == USER_STATUS_DISABLED)
            {
                static::$_error_code = \EC::USER_DISABLE_ERROR;

                return false;
            }
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    /**
     * 检测手机号是否有效
     *
     * @param string $phone
     *
     */
    public function CheckPhone(string $phone)
    {
        try
        {
            //1.0 获取用户信息
            $userAuth = UserAuth::instance();
            $userName = $userAuth->CheckExist_Phone($phone);
            if (!$userName)
            {
                static::$_error_code = \EC::USER_NOTEXIST_ERROR;

                return false;
            }

            $userInfo = User::instance()->GetInfo($userName);
            if ($userInfo['status'] == USER_STATUS_DISABLED)
            {
                static::$_error_code = \EC::USER_DISABLE_ERROR;

                return false;
            }
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    /**
     * 找回密码 -- 修改密码
     *
     * @param string $key     邮箱 | 手机号
     * @param string $new_pwd 新密码
     *
     * @return false|string
     */
    public function FindPwd_Reset(string $key, string $new_pwd)
    {
        try
        {
            $userName = $this->GetUserName($key);

            //2.0 获取用户信息
            $userInfo = User::instance()->GetInfo($userName);
            if (!$userInfo)
            {
                static::$_error_code = \EC::USER_NOTEXIST_ERROR;
                return false;
            }
            //验证账户是否可用
            if ($userInfo['status'] !== USER_STATUS_ENABLED)
            {
                static::$_error_code = \EC::USER_DISABLE_ERROR;
                return false;
            }

            //3.0 验证密码
            $userAuth  = UserAuth::instance();
            $loginInfo = $userAuth->GetInfo($userName);
            if (!$loginInfo)
            {
                static::$_error_code = \EC::USER_NOTEXIST_ERROR;
                return false;
            }

            // 判断新旧密码是否相同
            if ($this->Check_Pwd($loginInfo['pwd'], $new_pwd))
            {
                static::$_error_code = \EC::USER_OLD_PWD;
                return false;
            }

            if($userName == $new_pwd)
            {
                static::$_error_code = \EC::USER_PASSWD_SAME_ERROR;
                return false;
            }
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        Db::startTrans();
        try
        {
            //修改密码
            $userAuth->ModifyPwd($userName, $new_pwd);

            //非当前客户端下线
            $this->OffLine($userName);

            //操作日志
            $uL = UserLogs::instance();
            $uL->Add($userName, USERLOGOP_OP_TYPE_MODIFY, '修改密码', 'user', $userName);

            //发送通知
            $rtn   = true;
            $email = $loginInfo['email'];
            if (!empty($email))
            {
                //2.0 发送p
                $smtp       = yaconf('smtp');
                $emailTitle = $smtp['from_user'] . '账号';
                $copyRight  = $smtp['copy_right'];
                $nickname   = is_null($userInfo['nick_name']) ? ($userName) : ($userInfo['nick_name']);
                $body       = <<<EOF
        <table style='width:100%;max-width:960px;position: relative;left:0;right:0;margin: 0 auto;border-collapse: collapse;border-spacing: 0;font-size: 14px;line-height: 24px;color: #333;font-family: Microsoft YaHei;'>
      <tbody>
      <tr>
          <td style='padding: 20px 7.5% 0;'> <span style='border-bottom:1px dashed #ccc;z-index:1;' t='7' onclick='return false;' data='{$nickname}'>{$nickname}</span> ，您好！</td>
     </tr>
     
     <tr>
          <td style='padding: 20px 7.5% 0;'>您已成功修改{$emailTitle} <b style='margin: 0;text-decoration:none;'>{$email}</b> 密码。<br>若您未曾进行此操作，且认为有未经授权者访问您的账号，须尽快重置您的密码。密码重置后，请前往“账号中心”开启账号保护。</td>
     </tr>
     
      <tr>
          <td style='padding: 20px 7.5% 117px;'>此致<br>{$emailTitle}</td>
     </tr>
  </tbody></table>
  
  <table style='width:100%;max-width:960px;position: relative;left:0;right:0;margin: 0 auto;text-align: center;border-collapse: collapse;border-spacing: 0;font-size: 12px;line-height: 24px;font-family: Microsoft YaHei;'>
     <tbody>     
  <tr>
  <td style='display: block;height: 16px;border-top:#efefef solid 1px;background: -webkit-radial-gradient(top, ellipse farthest-side, rgba(251,251,251,1), rgba(255,255,255,0));background: -o-radial-gradient(top, ellipse farthest-side, rgba(251,251,251,1), rgba(255,255,255,0));background: -moz-radial-gradient(top, ellipse farthest-side, rgba(251,251,251,1), rgba(255,255,255,0));background: radial-gradient(top, ellipse farthest-side, rgba(251,251,251,1), rgba(255,255,255,0));'></td>
  </tr>
  
 <tr>
	  <td style='color: #999;padding:0 16px 28px;'>{$copyRight}</td>
 </tr>
   </tbody>
   </tbody></table>
EOF;

                $rtn = send_email($smtp, $email, $emailTitle . '密码已重置', $body);
            }

            Db::commit();

            return $rtn;
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    /**
     * 检测 Token 是否有效
     *
     * @param string $token_id
     *
     * @return array|bool
     */
    public function CheckToken(string $token_id)
    {
        try
        {
            $aot           = UserTokens::instance();
            $tokenUserInfo = $aot->GetInfo($token_id);

            if (!$tokenUserInfo) //令牌无效
            {
                static::$_error_code = \EC::ACCESSTOKEN_ERROR;

                return false;
            }

            if ($tokenUserInfo['status'] != USERTOKENS_STATUE_ENABLED) //异地登录，被迫下线
            {
                static::$_error_code = \EC::ACCESSTOKEN_OFFLINE_ERROR;

                return false;
            }

            if (Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT,
                    $tokenUserInfo['expire'])->diffInSeconds(Carbon::now()) < 0) //过期
            {
                static::$_error_code = \EC::ACCESSTOKEN_EXPIRED_ERROR;

                return false;
            }
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        return $tokenUserInfo;
    }

    /**
     * 掉线
     *
     * @param string $userName
     * @param int    $os_type windows/os x / linux / ios / android
     * @param string $token_id
     *
     * @return bool|false|int
     */
    public function OffLine(string $userName, int $os_type = null, string $token_id = null)
    {
        $aot    = UserTokens::instance();
        $tokens = $aot->GetOtherTokens($userName, $os_type, $token_id);

        if (is_array($tokens))
            return $aot->OffLine($tokens);

        return true;
    }

    //==================================================== 私有方法 =====================================================

    /**
     * 根据 用户名 | 手机号 | 邮箱 获取用户名
     *
     * @param string $key
     *
     * @return bool|mixed|string
     */
    private function GetUserName(string $key)
    {
        $userName = false;
        $userAuth = UserAuth::instance();
        do
        {
            if (validate_telphone($key))
            {
                $userName = $userAuth->CheckExist_Phone($key);
                break;
            }

            if (validate_email($key))
            {
                $userName = $userAuth->CheckExist_Email($key);
                break;
            }

            //此时认为它就是用户名
            $userName = $key;
        } while (0);

        if (!$userName)
        {
            static::$_error_code = \EC::USER_NOTEXIST_ERROR;

            return false;
        }

        return $userName;
    }

    /**
     * 检查名称是否有效
     *
     * @param string $name 用户名 | 手机号 | 邮箱
     *
     * @return bool|mixed|string
     */
    private function CheckName(string $name)
    {
        $userName = $this->GetUserName($name);

        //1.0 获取用户信息
        $userInfo = User::instance()->GetInfo($userName);
        if (!$userInfo)
        {
            static::$_error_code = \EC::USER_NOTEXIST_ERROR;

            return false;
        }

        //验证账户是否可用
        if ($userInfo['status'] !== USER_STATUS_ENABLED)
        {
            static::$_error_code = \EC::USER_DISABLE_ERROR;

            return false;
        }

        return $userName;
    }
}