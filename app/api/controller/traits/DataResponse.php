<?php

namespace app\api\controller\traits;

use app\api\logic\
{
    BaseLogic
};
use think\facade\
{Request};

/**
 * 返回数据
 */
trait DataResponse
{
    // +--------------------------------------------------------------------------
    // |  数据返回
    // +--------------------------------------------------------------------------
    /**
     * 返回请求结果(0不表示成功)
     *
     * @param int|null      $code 错误码(成功用200|null表示)
     * @param string|null   $msg 错误信息
     * @param null          $data 返回数据
     * @param int|null      $count 用在list列表,表示符合条件的总数.(注:不是count($data))
     * @param int|null      $took 耗时（毫秒）
     * @param string|null   $time 当前服务器时间
     * @param \Closure|null $callback 回调函数
     *
     * @return \think\Response
     */
    protected function R(
        int $code = null, string $msg = null, $data = null, int $count = null, int $took = null,
        string $time = null, \Closure $callback = null)
    {
        if (ob_get_length())
            ob_end_clean();

        //1.0 获取错误码，错误信息
        // 没有主动传递 code \ msg 时，从业务逻辑层获取
        if (!isset($code))
        {
            $code = BaseLogic::$_error_code;
        }
        if (!isset($msg))
        {
            $msg = BaseLogic::$_error_msg;
        }
        if ('' === $msg)
        {
            $msg = \EC::GetMsg($code);
        }

        $r = [
            'code' => $code,
            'msg'  => $msg,
        ];

        //2.0 获取记录个数
        if (isset($count))
        {
            $r['count'] = $count;
        }

        //3.0 took
        if (isset($took))
        {
            $r['took'] = $took;
        }

        //4.0 data
        if (isset($data))
        {
            //当 data 为 false 时，表示获取值失败
            if (!$data)
                $data = [];

            $r['result'] = $data;
        }

        //5.0 time
        if (isset($time))
        {
            $r['time'] = $time;
        }

        //----------------------------------------------------- 异步回调预处理 ------------------------------------------//
        if ($callback instanceof \Closure)
        {
            ob_start();
        }

        if ($callback instanceof \Closure)
        {
            global $g_callback;
            $g_callback[] = $callback;
        }

        if (Request::param('callback/s')) //jsonp
        {
            // ThinkPHP bug
            // Response类未处理jsonp类型 -- thinkphp/library/think/response/Jsonp.php
            // 当前台传递了 ['var_jsonp_handler' => 'callback'] 参数时，表示是jsonp请求；否则按照 json 请求处理
            return  \think\Response::create($r, 'jsonp', \EC::SUCCESS);
        }
        else if ($return_url = Request::param('return_url')) //redirect
        {
            $url = $return_url . '?sso_error=' . $msg;
            header("Location: $url", true, 307);
        }
        else if (strpos(Request::server('HTTP_ACCEPT'), 'image/') === 0) //image
        {
            //Output a 1x1px transparent image
            header('Content-Type: image/png');

            return \think\Response::create(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQ'
                . 'MAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZg'
                . 'AAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII='));
        }
        else //json
        {
            return \think\Response::create($r, 'json', \EC::SUCCESS);
        }
    }
}