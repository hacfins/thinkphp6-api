<?php

namespace app\common\middleware;

use app\api\controller\BaseController;
use app\api\controller\traits\DataCheck;
use app\api\logic\BaseLogic;
use app\api\logic\UserLogic;
use app\api\logic\UserLoginLogic;
use app\common\facade\Request;
use think\facade\
{Cache, Cookie, Session};

/**
 * 控制层 - 单点登录
 *
 * Class CSSO
 *
 * @package app\http\middleware
 */
class CSSO
{
    use DataCheck;

    public function handle(Request $request, \Closure $next)
    {
        if (ob_get_length())
            ob_end_clean();

        header('HTTP/1.1 200 OK');

        //获取登录信息
        BaseController::$_token = $this->GetToken($request);
        if (!BaseController::$_token)
        {
            BaseController::$_token = false;
        }
        $GLOBALS['token'] = BaseController::$_token;//供业务逻辑使用

        //登录用户请求
        if (BaseController::$_token)
        {
            //检测登录信息是否合法
            $rtn = \EC::SUCCESS;
            do
            {
                //0.0 检测是否cookie劫持
                $ipCityCache = Session::get(SESSIONID_USER_IP, false);
                if ($ipCityCache)
                {
                    $sorIp = $request->ip();

                    $bLocalIp = !filter_var($sorIp, FILTER_VALIDATE_IP,
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
                    if (!$bLocalIp)
                    {
                        //白名单中
                        $except = in_array($sorIp, yaconf('auth.exclude_ips'));
                        if (!$except)
                        {
                            $ipInfo = get_ip_info();
                            $ipCity = $ipInfo ? $ipInfo['city'] : '';
                            if ($ipCity && $ipCityCache != $ipCity)
                            {
                                $rtn = \EC::PARAM_SAFEERROR;
                                break;
                            }
                        }
                    }
                }

                //0.0 检测用户是否可用
                $userName = Session::get(SESSIONID_USER_NAME);
                if (!$userName)
                {
                    $rtn = \EC::USER_NOTEXIST_ERROR;
                    break;
                }
                if (!(new UserLogic())->CheckEnabled($userName))
                {
                    $rtn = BaseLogic::$_error_code;
                    break;
                }

                //1.0 获取用户名并缓存
                $tokenUserInfo = (new UserLoginLogic())->CheckToken(BaseController::$_token);
                if ($tokenUserInfo === false)
                {
                    $rtn = BaseLogic::$_error_code;
                    break;
                }

                //动态修改 session 对应的 cookie 的过期时间
                $this->SetCookieExpireTime($tokenUserInfo['expire']);
            } while (0);

            if ($rtn != \EC::SUCCESS)
            {
                Session::clear();
                cookie_clear();

                E($rtn, null, false);
            }

            $userName = strtolower($tokenUserInfo['user_name']);

            //记录用户名
            BaseController::$_uname = $userName;//供控制器使用
            $GLOBALS['user_name']   = $userName;//供业务逻辑使用

            //单设备登录
            $this->Single_Login($userName, BaseController::$_token);
        }

        return $next($request);
    }

    /**
     * 获取用户授权信息
     * Token: 优先从请求参数中获取Token，当请求参数中没有时，从Session中获取。
     *
     * @return mixed
     */
    private function GetToken(Request $request)
    {
        Session::init();

        //1.0 从请求参数中获取
        $token = $request->param('sg/s');
        if ($token)
        {
            if (CONTROLLER_NAME . '.' . ACTION_NAME != 'passport.user.open_url')
            {
                return $token;
            }

            //从客户端打开URL
            return false;
        }

        //2.0 说明是第三方扫描登录 - 只能被使用一次（登录完成后，立即删除）
        (new UserLoginLogic())->Session_CheckOther();

        //3.0 从 Session 中获取
        $tokenSession = Session::get(SESSIONID_USER_TOKEN);

        //说明有可能是第三方登录
        if (Cache::has(COOKIEID_USER_TOKEN . Session::getid()))
        {
            $tokenCookie = Cache::pull(COOKIEID_USER_TOKEN . Session::getid());
        }
        else
        {
            $tokenCookie = Cookie::get(COOKIEID_USER_TOKEN);
        }

        if (!$tokenSession || !$tokenCookie)
        {
            return false;
        }

        //被篡改
        if ($tokenSession != $tokenCookie)
        {
            Session::clear();
            cookie_clear();

            return false;
        }

        return $tokenSession;
    }

    /**
     * 设置 cookie 数据的过期时间
     *
     * @param $expire
     *
     * @throws \Exception
     */
    private function SetCookieExpireTime($expire = 0)
    {
        //动态修改 session 对应的 cookie 的过期时间
        $cookieConfig           = config('cookie');
        $cookieConfig['expire'] = (0 == $expire) ? 0 : (new \DateTime($expire));
        config($cookieConfig, 'cookie');
    }
}
