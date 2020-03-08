<?php

namespace app\common\middleware;

use app\api\logic\BaseLogic;
use app\api\logic\ImgLogic;
use app\common\facade\Request;

/**
 * 控制层 - 资源
 *
 * Class CCors
 *
 * 中间件主要用于拦截或过滤应用的 HTTP 请求，并进行必要的业务处理。
 *
 * @package app\http\middleware
 */
class CResource
{
    public function handle(Request $request, \Closure $next)
    {
        //【1】 index页面不需要检测
        if (CONTROLLER_NAME == 'base')
        {
            //首页
            if (ACTION_NAME == 'home_index')
                return $next($request);

            //output image
            if (ACTION_NAME == 'tmp_avatar' || ACTION_NAME == 'tmp_imgs')
            {
                //涉及图像截取跨域问题
                crossdomain_cors();

                $pathinfo = $request->pathinfo();
                $filePath = DIR_IMGS . $pathinfo;

                (new ImgLogic())->RtnPic($filePath);
                die();
            }
        }

        //【2.0】 用户身份信息
        BaseLogic::$_error_code = \EC::SUCCESS;
        BaseLogic::$_error_msg  = '';

        return $next($request);
    }
}
