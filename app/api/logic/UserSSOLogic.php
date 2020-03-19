<?php
namespace app\api\logic;

use app\common\third\CloudSSO;
use think\facade\Session;

/**
 * 单点登录
 */
class UserSSOLogic extends BaseLogic
{
    private $_broker = false;//broker

    /**
     * 初始化
     */
    private function SSO_Init()
    {
        if(session_status() != PHP_SESSION_ACTIVE)
            Session::init();

        //1.0 是否 attach SSO Server
        $this->_broker = new CloudSSO(yaconf('sso.server'), yaconf('sso.broker_id'), yaconf('sso.broker_secret'),
            yaconf('sso.access_key'), yaconf('sso.access_secret'));
    }

    /**
     * 根据token，获取用户信息
     *
     * @param string $cookieToken
     */
    public function SSO_GetUserInfo_Ex(string $cookieToken)
    {
        if (!$this->_broker)
        {
            $this->SSO_Init();
        }

        //调用 SSO Server 获取用户信息
        $param = [];

        $broker = $this->_broker;
        $info   = $broker->getUserInfo($param, true);

        //没有获取到用户信息
        if (!$info)
        {
            self::$_error_code = CloudSSO::$_error_code;
            self::$_error_msg = CloudSSO::$_error_msg;

            //清空缓存的消息
            Session::clear();
        }
        else
        {
            Session::set(SESSIONID_USER_TOKEN, $cookieToken);
            Session::set(SESSIONID_USER_INFO, $info);
        }

        $code = self::$_error_code;
        $errArr = [
            \EC::SSO_ATTACH_CHECK_ERROR,
            \EC::SSO_SESSIONKEY_NOT_ERROR,
            \EC::SSO_SESSION_EXIST_ERROR,
            \EC::SSO_SESSIONID_INVALID,
            \EC::API_ERR,
            \EC::ACCESSTOKEN_OFFLINE_ERROR,
            \EC::ACCESSTOKEN_EXPIRED_ERROR,
            \EC::ACCESSTOKEN_ERROR
        ];
        if (in_array($code, $errArr))
        {
            return false;
        }

        //出现其他错误码时，无需返回系统
        BaseLogic::$_error_code = \EC::SUCCESS;
        BaseLogic::$_error_msg  =  '';

        return true;
    }

    /**
     * 衔接用户中心，退出登录
     */
    public function SSO_LogOut()
    {
        do
        {
            if (!$this->_broker)
            {
                $this->SSO_Init();
            }

            $broker = $this->_broker;
            $rtn    = $broker->logOut(true);
            if(!$rtn)
            {
                self::$_error_code = CloudSSO::$_error_code;
                self::$_error_msg = CloudSSO::$_error_msg;
            }

            //没有获取到用户信息
            $code = self::$_error_code;
            if ($code == \EC::SSO_ATTACH_CHECK_ERROR || $code == \EC::SSO_SESSIONKEY_NOT_ERROR ||
                $code == \EC::SSO_SESSION_EXIST_ERROR || $code == \EC::SSO_SESSIONID_INVALID)
            {
                return false;
            }
        }while(0);

        Session::clear();

        return true;
    }

    /**
     * 根据用户名，获取用户信息
     *
     * @param string $user_name
     *
     * @return mixed
     */
    public function SSO_GetUserInfo(string $user_name)
    {
        do
        {
            if (!$this->_broker)
            {
                $this->SSO_Init();
            }

            $broker = $this->_broker;
            $info   = $broker->getUserInfoEx($user_name, [], true);
            if (!$info)
            {
                self::$_error_code = CloudSSO::$_error_code;
                self::$_error_msg  = CloudSSO::$_error_msg;
            }

            //没有获取到用户信息
            $code = BaseLogic::$_error_code;
            if ($code == \EC::SSO_ATTACH_CHECK_ERROR || $code == \EC::SSO_SESSIONKEY_NOT_ERROR ||
                $code == \EC::SSO_SESSION_EXIST_ERROR || $code == \EC::SSO_SESSIONID_INVALID)
            {
                return false;
            }

            return $info;
        }while(0);
    }
}