<?php

namespace app\common\middleware;

use app\api\controller\traits\IReflectionDef;
use app\api\logic\BaseLogic;
use app\api\logic\ProductLogic;
use app\common\facade\Request;


/**
 * 控制层 - 产品注册
 *
 * Class CProduct
 *
 * @package app\http\middleware
 */
class CProduct implements IReflectionDef
{
    public function handle(Request $request, \Closure $next)
    {
        //【6.0】 产品注册授权检测
        // 白名单中的API不需要产品注册授权检测
        if (!$this->IsWhite())
            $this->CheckProductRegister();

        return $next($request);
    }

    /**
     * 检测产品授权信息
     */
    private function CheckProductRegister()
    {
        $rtn = ProductLogic::Check();
        if (!$rtn)
        {
            E(BaseLogic::$_error_code, null, false);
        }
    }

    /**
     * 不需要产品验证的api
     *
     * @return bool
     */
    private function IsWhite()
    {
        $ruleName = MODULE_NAME . '-' . CONTROLLER_NAME . '.' . ACTION_NAME;
        if (in_array($ruleName, self::WHILE_LIST))
        {
            return true;
        }

        return false;
    }
}
