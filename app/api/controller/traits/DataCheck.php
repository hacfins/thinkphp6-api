<?php

namespace app\api\controller\traits;

use app\api\logic\UserLoginLogic;
use app\api\logic\WebsiteLogic;
use app\common\facade\Os;
use app\common\validate\ValidateEx;
use think\facade\Cache;

/**
 * 参数校验
 */
trait DataCheck
{
    // +--------------------------------------------------------------------------
    // |  数据校验
    // +--------------------------------------------------------------------------
    /**
     * 重写 验证数据 方法
     *
     * @access protected
     *
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @param mixed        $callback 回调方法（闭包）
     *
     * @return array|string|true
     */
    protected function validate($data, $validate, $message = [], $batch = true, $callback = null)
    {
        if (is_array($validate))
        {
            $v = new ValidateEx();
            $v->rule($validate);
        }
        else
        {
            if (strpos($validate, '.'))
            {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene))
            {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate)
        {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 单设备登录
     *
     * @param string $userName  用户名
     * @param string $userToken 当前用户认证信息
     *
     */
    protected function Single_Login(string $userName, string $userToken)
    {
        $SwitchInfo  = (new WebsiteLogic())->Switch_GetInfo();
        $singleLogin = $SwitchInfo['single_login'] ?? YES;

        $request = request();
        if (YES == $singleLogin)
        {
            $ip     = $request->ip();
            $except = in_array($ip, yaconf('auth.exclude_ips')); //白名单中
            $osType = osname_to_num(Os::getName());              //windows/os x / linux / ios / android等;

            //1.0 防止多登录（每个平台只能登录一个） 平台+IP 绑定
            if (!$except)
            {
                $cacheIPKey    = CACHE_USER_LOGIN_OS_INFO . $osType . '-' . $userName;
                $cacheTokenKey = $cacheIPKey . '_t';

                $IpCache    = Cache::get($cacheIPKey, false);
                $tokenCache = Cache::get($cacheTokenKey, false);

                //未登录过
                if ($IpCache === false)
                {
                    Cache::set($cacheIPKey, $ip, CACHE_TIME_DAY);
                    Cache::set($cacheTokenKey, $userToken, CACHE_TIME_DAY);
                }
                else//登录过
                {
                    //非同一IP
                    if (($ip != $IpCache) && ($userToken != $tokenCache))
                    {
                        Cache::set($cacheIPKey, $ip, CACHE_TIME_DAY);
                        Cache::set($cacheTokenKey, $userToken, CACHE_TIME_DAY);

                        //剔除其他终端，使其离线
                        $aot = new UserLoginLogic();
                        $aot->OffLine($userName, $osType, $userToken);
                    }
                }
            }
        }
    }

    /**
     * 接收并校验参数,并将验证后的参数保存在self::$_input中
     *
     * @param array $paramsInfo 参数列表 四项依次为:参数名,默认值,tp的强制转换类型(s,d,a,f,b),tp的验证规则
     *                          eg:[
     *                          [ 'age',  18, 'd',  'number|<=:150|>:0'],
     *                          [ 'sex',  null, 's',  'require'],
     *                          ]
     *
     * @return array|string|true
     * @author jiangjiaxiong
     *
     */
    protected function I($paramsInfo)
    {
        try
        {
            //数据接收&校验
            $request = Request();

            $toVali = false;
            $params = $rule = [];
            foreach ($paramsInfo as $paramInfo)
            {
                $paramInfo[0] = $paramInfo[0] ?? null;
                $paramInfo[1] = $paramInfo[1] ?? null;
                $paramInfo[2] = $paramInfo[2] ?? null;
                $paramInfo[3] = $paramInfo[3] ?? null;

                if (!is_array($paramInfo) || !$paramInfo[0])
                {
                    continue;
                }

                //$parmInfo[0] 参数名
                $paramName = "{$paramInfo[0]}";

                //$parmInfo[2] tp的强制转换类型
                if (in_array($paramInfo[2], [
                    's',
                    'd',
                    'b',
                    'a',
                    'f',
                ]))
                {
                    $paramName .= "/{$paramInfo[2]}";
                }

                //$parmInfo[1] 默认值
                if (isset($paramInfo[1]))
                {
                    $params[$paramInfo[0]] = $request->param($paramName, $paramInfo[1]);
                }
                else
                {
                    $params[$paramInfo[0]] = $request->param($paramName);
                }

                //$parmInfo[3] tp的验证规则
                if (is_string($paramInfo[3]))
                {
                    $rule[$paramInfo[0]] = $paramInfo[3];
                    $toVali              = true;
                }

            }

            $params = $params;
            if ($toVali)
            {
                //当参数校验有误时，会抛出异常
                $this->validate($params, $rule);
            }

            return $params;
        }
        catch (\Throwable $e)
        {
            E(\EC::PARAM_ERROR, $e->getMessage(), false);
        }
    }
}