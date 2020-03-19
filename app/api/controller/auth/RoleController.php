<?php
namespace app\api\controller\auth;

use app\api\controller\BaseController;
use app\api\logic\
{
    RoleLogic
};

/**
 * 角色管理
 */
class RoleController extends BaseController
{
    public function Index()
    {
        return $this->R();
    }

    // +----------------------------------------------------------------------------------------------------------------
    // | 角色
    // +----------------------------------------------------------------------------------------------------------------
    /**
     * 添加角色
     */
    public function Add()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //接收参数
        $vali = $this->I([
            [
                'role_name',
                null,
                's',
                'require|length:1,20',
            ],
            [
                'description',
                '',
                's',
                'length:0,128',
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $role_id = (new RoleLogic())->Add(self::$_input['role_name'], ROLE_TYPE_GENERAL, self::$_input['description']);

        return $this->R(null, null, ['role_id' => $role_id ? $role_id : '']);
    }

    /**
     * 修改角色
     */
    public function Modify()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //接收参数
        $vali = $this->I([
            [
                'role_id',
                null,
                's',
                'require',
            ],
            [
                'role_name',
                null,
                's',
                'length:1,20'
            ],
            [
                'description',
                null,
                's',
                'length:0,128'
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        (new RoleLogic())->Modify(self::$_input['role_id'], self::$_input['role_name'], null, self::$_input['description']);

        return $this->R();
    }

    /**
     * 移动角色
     */
    public function Move_To()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //接收参数
        $vali = $this->I([
            [
                'id',
                null,
                's',
                'require',
            ],
            [
                'des_id',
                null,
                's',
                'require',
            ],
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        (new RoleLogic())->MoveTo(self::$_input['id'], self::$_input['des_id']);

        return $this->R();
    }

    /**
     * 角色名称是否存在
     */
    public function Exist_Name()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        $vali = $this->I([
            [
                'role_name',
                null,
                's',
                'require|length:1,20',
            ],
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $exist = (new RoleLogic())->ExistName(self::$_input['role_name']);

        return $this->R(null, null, ['exist' => $exist]);
    }

    /**
     * 删除角色
     */
    public function Del()
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
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        (new RoleLogic())->Del(self::$_input['role_id']);

        return $this->R();
    }

    /**
     * 获取角色信息
     */
    public function Info()
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
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $info = (new RoleLogic())->GetInfo(self::$_input['role_id']);

        return $this->R(null, null, $info ? $info : []);
    }

    /**
     * 获取角色列表
     */
    public function Get_List()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        $vali = $this->I([
            [
                'role_type',
                null,
                'd',
                'number|in:' . ROLE_TYPE_SET,
            ],
            [
                'order_field',
                'sort',
                's',
                'length:1,20',
            ],
            [
                'is_asc',
                YES,
                'd',
                'in:' . YES . ',' . NO
            ],
            [
                'page',
                DEF_PAGE,
                'd',
                'number|>=:1',
            ],
            [
                'page_size',
                DEF_PAGE_SIZE,
                'd',
                'between:1,' . DEF_PAGE_MAXSIZE
            ],
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $list = (new RoleLogic())->GetList(self::$_input['role_type'], self::$_input['order_field'], self::$_input['is_asc'], self::$_input['page'], self::$_input['page_size']);

        return $list ? $this->R(null, null, $list[0], $list[1]) : $this->R();
    }
}