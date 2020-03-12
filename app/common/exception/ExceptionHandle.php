<?php

namespace app\common\exception;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Env;
use think\Response;
use Throwable;

/**
 * Http 异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     */
    protected $ignoreReport = [
        //        HttpException::class,
        //        HttpResponseException::class,
        //        ModelNotFoundException::class,
        //        DataNotFoundException::class,
        //        ValidateException::class,
    ];

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): Response
    {
        header('HTTP/1.1 200 OK');

        //1.0 获取错误码与错误信息
        if ($e instanceof HttpException)
        {
            $errCode = $e->getStatusCode();
        }
        if (!isset($errCode))
        {
            $errCode = $e->getCode();
        }

        $msg = $e->getMessage();

        //处理参数错误
        if (strpos($msg, 'method param miss') !== false)
        {
            $arr = explode(':', $msg);

            $errCode = \EC::PARAM_ERROR;
            $msg     = \EC::GetMsg($errCode) . ': ' . $arr[1];
        }

        $result = [
            'code' => $errCode,
            'msg'  => $msg,
        ];

        //2.0 开发模式，输出详细的 trace 信息
        // 自定义的 ResponseException 异常不需要返回 trace 信息
        if (Env::get('app_debug') && !($e instanceof ResponseException))
        {
            $result['error']['file']  = $e->getFile();
            $result['error']['line']  = $e->getLine();
            $result['error']['trace'] = $e->getTrace();
        }
        //生产模式
        else
        {
            //只显示自定义的异常信息
            if (!\EC::Has($errCode))
            {
                $result['msg'] = '程序出错,请联系管理员';
            }
        }

        //将0转为500
        if ($result['code'] === 0)
        {
            $result['code'] = \EC::API_ERR;
        }

        //返回错误信息
        if ($request->param('callback/s'))
        {
            return Response::create($result, 'jsonp', \EC::SUCCESS);
        }
        else
        {
            return Response::create($result, 'json', \EC::SUCCESS);
        }
    }
}