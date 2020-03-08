<?php
namespace app\common\middleware;

use app\common\facade\Request;

/**
 * 控制层 - 控制器开始
 */
class CBegin
{
    public function handle(Request $request, \Closure $next)
    {
        define('MODULE_NAME', strtolower(app('http')->getName()));

        define('CONTROLLER_NAME', strtolower($request->controller()));

        define('ACTION_NAME', strtolower($request->action()));

        //预处理日志类型
        $this->PrepareLog();

        return $next($request);
    }

    // +--------------------------------------------------------------------------
    // |  私有方法
    // +--------------------------------------------------------------------------
    /*
    * 预处理日志类型
    */
    protected function PrepareLog()
    {
        $control   = CONTROLLER_NAME;
        $method    = ACTION_NAME;
        $action    = $control . '.' . $method;
        $methodArr = ['user.user.modifyphone', 'user.user.modifyemail', 'user.user.modifypwd', 'user.user.modifyinfo',
                      'passport.wxlogin.delbind', 'user.message.add'];

        //1.0 only type of modify need record log
        $adds    = ['add', 'import'];
        $modifys = [
            'modify', 'move', 'update', 'set', 'audit', 'review', 'reload', 'change', 'enabled'
        ];
        $dels    = ['del', 'remove'];

        global $g_logs_optype;
        global $g_logs_insert;

        $g_logs_optype = LOGOP_OP_TYPE_ADD;
        $opMethods     = [
            LOGOP_OP_TYPE_ADD    => $adds,
            LOGOP_OP_TYPE_MODIFY => $modifys,
            LOGOP_OP_TYPE_REMOVE => $dels
        ];

        foreach ($opMethods as $key => $methodItems)
        {
            if($g_logs_insert)
            {
                break;
            }

            foreach ($methodItems as $methodItem)
            {
                if ((!in_array($action, $methodArr) && false !== stripos($method, $methodItem)))
                {
                    $g_logs_optype = $key;
                    $g_logs_insert = true;
                    break;
                }
            }
        }

        return true;
    }
}