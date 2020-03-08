<?php

namespace app\common\middleware;

use app\api\controller\traits\IReflectionDef;
use app\api\logic\
{AuthLogic, BaseLogic, WebsiteLogic};
use app\common\third\CloudSignature;
use Carbon\Carbon;
use think\facade\Cache;
use app\common\facade\Request;
use think\facade\Log;
use think\facade\Session;

/**
 * 控制层 - API 频率、授权、清空缓存
 *
 * Class CAPI
 *
 * @package app\http\middleware
 */
class CAPI implements IReflectionDef
{
    public function handle(Request $request, \Closure $next)
    {
        //【4.0】 API 调用频率限制
        $SwitchInfo = (new WebsiteLogic())->Switch_GetInfo();
        $apiLimit = $SwitchInfo['api_limit'] ?? YES;
        if ($apiLimit == YES)
            $this->API_Limit();

        //【5.0】 API应用接入授权
        $this->AuthSignature();

        //【7.0】 API权限控制
        //Todo:开发阶段，可以临时屏蔽
        $this->CheckApiAuth();

        //【8.0】 清空临时文件
        $this->ClearFile([
            runtime_path(),
            root_path() . 'uploads/'
        ]);

        return $next($request);
    }

    // +--------------------------------------------------------------------------
    // |  授权检测
    // +--------------------------------------------------------------------------
    private function API_Limit($limit = null)
    {
        if (!$limit)
            $limit = Session::getid();

        $key    = CACHE_USER_LIMIT . $limit;
        $keyCnt = $key . 'cnt';

        // Create the key if it doesn't exist
        $count = Cache::get($keyCnt);
        if (!$count)
        {
            Cache::set($keyCnt, 0, 60);
        }

        // Increment by 1
        if (Cache::inc($keyCnt) > SWITCH_API_LIMIT_TIMES) // Fail if minute requests exceeded
        {
            E(\EC::ACCESSTOKEN_LIMIT_ERROR, null, false);
        }
    }

    /**
     * API 应用接入授权[ AccessKey 和 SecretKey ]
     */
    private function AuthSignature()
    {
        try
        {
            $request = Request();

            //1.0 不需要接入授权
            $referer = $request->header('referer');
            if (!is_null($referer)) //UI平台
            {
                $arr = parse_url($referer);
                $ip  = $arr['host'];

                //UI 与 API 部署在同一台机器时，不需要授权
                $reqADDR = $request->server('SERVER_ADDR') ?? '';
                $reqNAME = $request->server('SERVER_NAME') ?? '';
                if (($reqNAME == $ip) || ($reqADDR == $ip))
                    return true;

                //不需要API授权的地址
                if (in_array($ip, yaconf('auth.exclude_ips')))
                    return true;
            }
            else //第三方
            {
                //同台机器
                $ip = $request->ip();
                if (in_array($ip, yaconf('auth.exclude_ips')))
                    return true;
            }

            //微信平台，移除认证
            $ruleName = MODULE_NAME . '-' . CONTROLLER_NAME . '.' . ACTION_NAME;
            if (in_array($ruleName, [
                'api-passport.wxlogin.baseinfo',
                'api-passport.wxlogin.userinfo',
                'api-passport.user.open_url']))
            {
                return true;
            }

            //2.0 非UI平台
            $headers = $request->header();

            //1.0 获取签名
            $signature = $headers['signature'];
            if (!isset($signature))
            {
                E(\EC::AUTH_API_ERROR, null, false);
            }

            $signatureParams = [
                'signaturemethod'  => $headers['signaturemethod'],
                'signaturenonce'   => $headers['signaturenonce'], //防止截获攻击
                'signatureversion' => $headers['signatureversion'],
                'accesskey'        => $headers['accesskey'],
                'timestamp'        => $headers['timestamp'], //防止截获攻击
                'format'           => $headers['format'],
            ];

            // access_secret
            $authKeys      = yaconf('auth.apikeys');
            $access_secret = $authKeys[$signatureParams['accesskey']] ?? false;
            if (!$access_secret)
            {
                E(\EC::AUTH_API_ACCESSKEY_ERROR, null, false);
            }

            // 只保留用户的请求参数
            if ($request->ispost())
            {
                $params = $request->post();
            }
            else
            {
                $params = $request->get();
            }

            // 校验
            $signatureCheck = (new CloudSignature())->signature($access_secret, array_merge($params, $signatureParams));
            if ($signatureCheck != $signature)
            {
                E(\EC::AUTH_API_SECRETKEY_ERROR, null, false);
            }
        }
        catch (\Throwable $e)
        {
            E(\EC::AUTH_API_ERROR, $e->getMessage());
        }

        return $signatureParams['accesskey'];
    }

    /**
     * 先行方法 - api权限验证
     * @author jiangjiaxiong
     * @date
     */
    private function CheckApiAuth()
    {
        if (!(new AuthLogic())->Check(self::WHILE_LIST))
        {
            E(BaseLogic::$_error_code, null, false);
        }
    }

    // +--------------------------------------------------------------------------
    // |  定期清空临时文件
    // +--------------------------------------------------------------------------
    /**
     * 清空目录中 N 天前的数据
     *
     * @param array $dirPaths 目录全路径
     *
     * @return bool
     */
    protected function ClearFile(array $dirPaths)
    {
        $key = CACHE_ONCE . date('Y-m-d', time());

        //因为影响性能，一天只执行一次
        $runOnce = Cache::get($key);
        try
        {
            if (!$runOnce)
            {
                Cache::set($key, YES, CACHE_TIME_DAY);

                foreach ($dirPaths as $dirPath)
                {
                    if (!$dirPath)
                    {
                        continue;
                    }

                    $this->ClearDir($dirPath);
                }
            }
        }
        catch (\Throwable $e)
        {
            Log::error($e->getMessage());
        }

        return true;
    }

    private function ClearDir($dirPath)
    {
        $clearTime = Carbon::now()->subDays(DIR_TEMP_CLEAR_DAYS)->timestamp;

        foreach (new \DirectoryIterator($dirPath) as $fileInfo)
        {
            if ($fileInfo->isDot())
            {
                continue;
            }

            //1.0 目录时，递归执行
            if ($fileInfo->isDir())
            {
                $this->ClearDir($fileInfo->getRealPath());
            }
            //2.0 删除 N 天前的文件
            else if ($fileInfo->isFile() || $fileInfo->isLink())
            {
                if ($fileInfo->getCTime() < $clearTime)
                {
                    unlink($fileInfo->getRealPath());
                }
            }
        }

        return true;
    }
}
