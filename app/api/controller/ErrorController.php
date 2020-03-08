<?php
// +----------------------------------------------------------------------
// | 控制器容错(TP的特殊控制器,不处理具体业务逻辑)
// +----------------------------------------------------------------------
namespace app\api\controller;
use app\common\facade\Request;

class ErrorController extends BaseController
{
    public function index(Request $request)
    {
        return $this->R(\EC::URL_ERROR, strtolower(' ' . $request->domain() . '/' . MODULE_NAME . '/' . $request->controller() . '/' . $request->action() . ' 不存在'), '请求地址错误');
    }

    public function _empty(Request $request)
    {
        return $this->R(\EC::URL_ERROR, strtolower(' ' . $request->domain() . '/' . MODULE_NAME . '/' . $request->controller() . '/' . $request->action() . ' 不存在'), '请求地址错误');
    }
}