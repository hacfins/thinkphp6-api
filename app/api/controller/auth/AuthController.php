<?php
namespace app\api\controller\auth;

use app\api\controller\BaseController;
use app\api\controller\traits\IReflectionDef;
use app\api\controller\traits\Reflection;
use app\api\logic\
{
    AuthLogic
};

/**
 * 权限管理
 */
class AuthController extends BaseController implements IReflectionDef
{
    use Reflection;

    public function Index()
    {
        return $this->R();
    }

    // +----------------------------------------------------------------------------------------------------------------
    // | 权限
    // +----------------------------------------------------------------------------------------------------------------
    /**
     * 刷新规则列表
     */
    public function Refresh()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        (new AuthLogic())->ReNewRules( $this->GetPublicMethods() );

        return $this->R();
    }

    /**
     * 设置权限规则
     */
    public function Set_Rules()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        $vali = $this->I([
            [
                'role_id',
                null,
                's',
                'require',
            ],
            [
                'rules',
                null,
                's',
                'require',
            ],
            [
                'module',
                null,
                's',
                'length:1,32'
            ],
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        (new AuthLogic())->SetRules(self::$_input['role_id'], self::$_input['rules'], self::$_input['module']);

        return $this->R();
    }

    /**
     * 获取权限列表
     */
    public function Get_List()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        $vali = $this->I([
            [
                'role_id',
                null,
                's',
                'require',
            ],
            [
                'module',
                null,
                's',
                'length:1,32'
            ],
            [
                'order_field',
                'module',
                's',
                'length:1,20',
            ],
            [
                'is_asc',
                YES,
                'd',
                'in:' . YES . ',' . NO
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $res = (new AuthLogic())->GetList(self::WHILE_LIST, self::$_input['role_id'], self::$_input['module'],
            self::$_input['order_field'], self::$_input['is_asc']);
        if ($res)
            return $this->R(null, null, $res);

        return $this->R();
    }

    // +----------------------------------------------------------------------------------------------------------------
    // | View
    // +----------------------------------------------------------------------------------------------------------------
    /**
    /**
     * 显示/隐藏 列表
     *
     * @date
     */
    public function View_List()
    {
        $res = (new AuthLogic())->ViewList();

        return $this->R(null, null, $res);
    }
}