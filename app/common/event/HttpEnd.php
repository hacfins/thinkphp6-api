<?php
namespace app\common\event;

use app\api\logic\UserLogLogic;
use app\common\exception\Debug;
use string\P4String;
use think\Response;
use app\common\facade\Request;

/**
 * -输出结束
 */
class HttpEnd
{
    public function handle(Request $request, Response $response)
    {
        // write log info into database
        $logInfo = $this->GetLogInfo($request, $response);
        if ($logInfo)
        {
            global $g_logs_opid;
            (new UserLogLogic())->Add($g_logs_opid, $logInfo['op'], $logInfo['op_type'], $logInfo['op_url'],
                $logInfo['op_params'], $logInfo['op_result'], $logInfo['op_comment'],
                $logInfo['use_time'], $logInfo['use_io'], $logInfo['use_mem']);
        }

        //------------------------------------------------------- 异步回调开始 ------------------------------------------//
        global $g_callback;
        if($g_callback)
        {
            foreach ($g_callback as $callback)
            {
                if ($callback instanceof \Closure)
                {
                    // get the size of the output
                    $size = ob_get_length();
                    // send headers to tell the browser to close the connection
                    header("Content-Length: $size");
                    header('Connection: close');

                    // 响应完成, 立即返回到前端,关闭连接
                    ob_end_flush();
                    if (ob_get_length())
                        ob_flush();
                    flush();

                    if (function_exists("fastcgi_finish_request"))  // yii或yaf默认不会立即输出，加上此句即可（前提是用的fpm）
                        fastcgi_finish_request(); // 响应完成, 立即返回到前端,关闭连接

                    // exec asyc task
                    ignore_user_abort(true);//ignore close browser
                    set_time_limit(60 * 10); //exec duration 10 min
                    call_user_func_array($callback, []);
                }
            }
        }
    }

    // +--------------------------------------------------------------------------
    // |  私有方法
    // +--------------------------------------------------------------------------
    /**
     * get log infos
     * return
     *      [
     *       'op',
     *       'op_type',
     *       'op_url',
     *       'op_params',
     *       'op_result',
     *       'op_comment',
     *       'use_time',
     *       'use_io',
     *       'use_mem'
     *      ]
     */
    protected function GetLogInfo(Request $request, Response $response)
    {
        $logArr = [];

        global $g_logs_insert;
        if ($g_logs_insert)
        {
            $control = MODULE_NAME . '-' . CONTROLLER_NAME;
            $method  = ACTION_NAME;

            //2.0 action，like modify course
            $apiLang      = include_once(CONF_PATH . 'rule_zh.php');
            $logArr['op'] = $apiLang[$control][$method] ?? $method;

            //2.1 type，like add、del、modify...
            global $g_logs_optype;
            $logArr['op_type'] = $g_logs_optype;

            //2.2 request url (dont contain url ext)
            $logArr['op_url'] = $request->pathinfo();

            //2.3 params
            $logArr['op_params'] = '';
            $params              = $request->param();
            if ($params)
            {
                //remove path param
                array_shift($params);
                //remove system param
                $delKeys = [
                    'callback',
                    '_',
                    'control',
                    'action'
                ];
                foreach ($params as $key => $item)
                {
                    if (in_array($key, $delKeys))
                    {
                        unset($params[$key]);
                    }
                }
                $logArr['op_params'] = P4String::jsonencode($params);
            }

            //2.4 result（success/fail/error)
            $respData            = $response->getData();
            $logArr['op_result'] = $respData['code'] ?? \EC::API_ERR;

            //2.5 description
            $logArr['op_comment'] = '';
            if (\EC::SUCCESS != $logArr['op_result'])
            {
                $logArr['op_comment'] = $respData['msg'] ?? '';
            }
            else
            {
                global $g_logs_comment;
                if($g_logs_comment)
                {
                    $logArr['op_comment'] = $g_logs_comment;
                }
            }

            //3.1 use time(微秒)
            $logArr['use_time'] = Debug::getUseTime() * 1000000;

            //3.2 req / s
            $useIO            = Debug::getThroughputRate();
            $logArr['use_io'] = substr($useIO, 0, strlen($useIO) - strlen('req/s'));

            //3.3 use memory
            $logArr['use_mem'] = Debug::getUseMem();

            return $logArr;
        }

        return false;
    }
}