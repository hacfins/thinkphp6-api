<?php
namespace app\api\logic\traits;

use app\api\model\
{
    log\UserLogs, rbac\UserTokens
};
use app\common\{
    facade\Os
};
use think\facade\
{Cache, Cookie, Session};

/*
 * 用户操作
 */
trait UserOp
{
    // +----------------------------------------------------------------------------------------------------------------
    // | 登录
    // +----------------------------------------------------------------------------------------------------------------
    /**
     * 用户登录相关操作
     *
     * @param $userName
     * @param $bFreelogin
     *
     * @return false|string
     */
    protected function Login_Login($userName, $bFreelogin=NO, $error_session=null)
    {
        //【1】分配令牌
        $aot   = UserTokens::instance();
        $token = $aot->Add($userName, $bFreelogin);

        //2.0 新的 session_id 号
        //账号登录 || 点击手持端的“微信登录”按钮登录
        if(!$error_session || ($error_session == Session::getid()))
        {
            $exist = Cookie::get(config('session.name'), '');
            if($exist)
                @Session::regenerate(true);

            //Session 设置
            $this->Session_Login($token, $userName, $bFreelogin);
        }
        else//说明是第三方扫描登录
        {
            //【2】登录日志-报表，在调用 CheckQrCode 时，入口中的 GetToken 增加
            $this->Session_OtherLogin($error_session, $token, $userName);
        }

        $ipInfo = get_ip_info();
        $ipCity = $ipInfo ? $ipInfo['city'] : '';
        if($ipCity)
        {
            Session::set(SESSIONID_USER_IP, $ipCity);
        }

        return $token;
    }

    /**
     * 用户退出登录相关操作
     *
     * @param $userName
     */
    protected function Login_out($token_id)
    {
        //【1】分配令牌
        $aot = UserTokens::instance();
        $aot->DelByTokens([$token_id]);

        //【2】清空session数据
        Session::clear();
        cookie_clear();
    }

    // +----------------------------------------------------------------------------------------------------------------
    // | 登录 - Session
    // +----------------------------------------------------------------------------------------------------------------
    /**
     * 成功登录的 Session 设置
     *
     * @param string $token
     * @param string $userName
     */
    protected function Session_Login(string $token, string $userName, $bFreelogin=false)
    {
        $expire = 0;
        if(YES == $bFreelogin)
        {
            $expire = USERTOKENS_TOKEN_EXPIRES_LONG;
        }

        //【4】设置登录信息
        Session::set(SESSIONID_USER_TOKEN, $token);
        Session::set(SESSIONID_USER_NAME, $userName);

        //供其他子系统使用
        Cookie::set(COOKIEID_USER_TOKEN, $token, $expire);
        //Cookie::set 下次才会生效
        Cache::set(COOKIEID_USER_TOKEN . Session::getid(), $token);

        //登录日志
        $this->Lg_Login($userName, $token);
    }

    /**
     * 第三方扫描登录的 Session 设置
     *
     * @param string $otherSessionId
     * @param string $token
     * @param string $userName
     */
    protected function Session_OtherLogin(string $otherSessionId, string $token, string $userName)
    {
        //【2】登录日志-报表，在调用 CheckQrCode 时，入口中的 GetToken 增加
        Cache::set(OTHER_LOGIN . $otherSessionId, [
            SESSIONID_USER_TOKEN => $token,
            SESSIONID_USER_NAME  => $userName,
        ], 60);
    }

    /**
     * 第三方登录检测
     */
    public function Session_CheckOther()
    {
        //说明是微信扫描登录 - 只能被使用一次（登录完成后，立即删除）
        $otherInfoArr = Cache::pull(OTHER_LOGIN . Session::getid());
        if ($otherInfoArr)
        {
            $otherToken    = $otherInfoArr[SESSIONID_USER_TOKEN] ?? null;
            $otherUserName = $otherInfoArr[SESSIONID_USER_NAME] ?? null;

            $this->Session_Login($otherToken, $otherUserName, false);
        }
    }

    /**
     * 验证密码的正确性
     *
     * @param string $hashPwd created by password_hash()
     * @param string $userPwd The user's password.
     *
     * @return bool
     */
    protected function Check_Pwd(string $hashPwd, string $userPwd)
    {
        if(yaconf('encrypt.pwd') && strlen($userPwd) != 32) //md5 加密传输
        {
            $userPwd = md5($userPwd);
        }

        //验证密码
        $pwdInfo = password_get_info($hashPwd);
        if ($pwdInfo['algo'] == 0) //以前的密码机制
        {
            if ($hashPwd !== md5($userPwd . CRYPT_SALT))
            {
                return false;
            }
        }
        else //新密码机制，安全系数更高
        {
            if (!password_verify($userPwd, $hashPwd))
            {
                return false;
            }
        }

        return true;
    }

    // +----------------------------------------------------------------------------------------------------------------
    // | 日志
    // +----------------------------------------------------------------------------------------------------------------
    /**
     * 登录 - 日志 + 统计
     *
     * @param string $userName  用户名
     * @param string $token_id  登录的token_id
     */
    public function Lg_Login(string $userName, string $token_id)
    {
        //登录日志
        $uL = UserLogs::instance();
        $uL->Add($userName, USERLOGOP_OP_TYPE_LOGIN, USERLOGOP_OP_DETAIL_LOGIN, 'user_tokens', $token_id);

        //统计信息
        if(Os::isMobile())
        {

        }
        else
        {

        }
    }

    /**
     * 新增用户
     *
     * @param string $userName
     */
    protected function Lg_AddUser(string $userName)
    {
        //日志
        $uL = UserLogs::instance();
        $uL->Add($userName, USERLOGOP_OP_TYPE_ADD, USERLOGOP_OP_DETAIL_ADD, 'user', $userName);

        //统计信息
    }
}