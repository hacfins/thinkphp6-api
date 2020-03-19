<?php
namespace app\api\logic;

use app\api\logic\traits\ImgProcess;
use app\api\model\rbac\User;

/*
 * 业务逻辑基类
 */
class BaseLogic
{
    use ImgProcess;

    public static $_error_code = \EC::SUCCESS;
    public static $_error_msg  = '';

    protected static $_uname = false; //用户名
    protected static $_uinfo = false; //用户信息 - SSO
    protected static $_token = false; //用户令牌

    // +----------------------------------------------------------------------
    // | 用户：
    // | 控制层路由到业务逻辑层，为业务逻辑层操作提供方便
    // +----------------------------------------------------------------------
    public function __construct()
    {
        //用户名
        if (isset($GLOBALS['user_name']))
        {
            self::$_uname = $GLOBALS['user_name'];
        }

        //用户Token
        if (isset($GLOBALS['token']))
        {
            self::$_token = $GLOBALS['token'];
        }

        //用户info
        if (isset($GLOBALS['uinfo']))
        {
            self::$_uinfo = $GLOBALS['uinfo'];
        }
    }

    /**
     * 判断是否是管理员
     *
     * @author jiangjiaxiong
     * @date
     * @return bool
     */
    protected function IsAdmin()
    {
        //超级管理员
        if (self::$_uname && (self::$_uname == 'admin'))
        {
            return true;
        }

        return false;
    }

    /**
     * 用户昵称
     *
     * @param $user_name
     *
     * @return string
     */
    protected function GetNickName($user_name)
    {
        $arr = [
            USER_NAME_SYS,
            USER_NAME_UNKOWN,
        ];
        if (in_array($user_name, $arr))
        {
            return $user_name;
        }

        $user = User::instance();
        return $user->GetNickName($user_name ?? '');
    }
}