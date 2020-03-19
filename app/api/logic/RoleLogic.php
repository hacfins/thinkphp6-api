<?php

namespace app\api\logic;

use app\api\model\
{
    rbac\Role, rbac\RoleRules, rbac\UserRoles
};
use think\facade\Db;

/**
 * 角色
 */
class RoleLogic extends BaseLogic
{
    /**
     * 角色创建
     *
     * @param string $name
     * @param int    $role_type
     * @param string $description
     *
     * @return bool|string
     */
    public function Add(string $name, int $role_type = ROLE_TYPE_GENERAL, string $description = '')
    {
        $role = Role::instance();

        //1.0 检验角色名是否存在
        if ($role->CheckNameExist($name))
        {
            static::$_error_code = \EC::ROLE_NAME_EXIST_ERROR;

            return false;
        }

        //2.0 添加角色
        $role_id = $role->Add(self::$_uname, $name, $role_type, $description);

        return $role_id;
    }

    /**
     * 修改角色
     *
     * @param string      $role_id
     * @param string|null $role_name
     * @param string|null $description
     * @param int|null    $role_type
     *
     * @return bool|string
     */
    public function Modify(string $role_id, string $role_name = null, int $role_type = null, string $description = null)
    {
        $role = Role::instance();

        //1.0 判断角色是否存在
        $info = $role->GetInfo($role_id);
        if (!$info)
        {
            static::$_error_code = \EC::ROLE_NOTEXIST_ERROR;

            return false;
        }
        //2.0 检验角色名
        if (isset($role_name) && $role->CheckNameExist($role_name, $role_id))
        {
            static::$_error_code = \EC::ROLE_NAME_EXIST_ERROR;

            return false;
        }

        //3.0 修改角色
        $role->Modify($role_id, $role_name, $role_type, null, $description);

        return true;
    }

    /**
     * 移动角色
     *
     * @param string $id
     * @param string $des_id
     *
     * @return bool|string
     */
    public function MoveTo(string $id, string $des_id)
    {
        $role = Role::instance();

        $sorInfo = $role->GetInfo($id);
        $desInfo = $role->GetInfo($des_id);

        $sorSort = $sorInfo['sort'] ?? null;
        $desSort = $desInfo['sort'] ?? null;
        if (!$sorSort || !$desSort)
        {
            static::$_error_code = \EC::ROLE_NOTEXIST_ERROR;

            return false;
        }

        Db::startTrans();
        try
        {
            //所谓的sort，其实就是sort值替换
            $role->Modify($id, null, null, $desSort);
            $role->Modify($des_id, null, null, $sorSort);

            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * 检测角色名称是否存在
     *
     * @param string $role_name
     *
     * @return int
     */
    public function ExistName(string $role_name)
    {
        $role = Role::instance();

        return $role->CheckNameExist($role_name) ? YES : NO;
    }

    /**
     * 删除角色
     *
     * @param string $role_id
     */
    public function Del(string $role_id)
    {
        $role = Role::instance();

        //1.0 检查角色下是否存在用户
        if ($this->CheckUserExist($role_id))
        {
            static::$_error_code = \EC::ROLE_USER_EXIST_ERROR;

            return false;
        }

        //2.0 检查角色是否存在
        $info = $role->GetInfo($role_id);
        if (!$info)
        {
            static::$_error_code = \EC::ROLE_NOTEXIST_ERROR;

            return false;
        }

        //3.0 系统角色不可删除
        if ($info['role_type'] == ROLE_TYPE_SYSTEM)
        {
            static::$_error_code = \EC::ROLE_CANT_DELETE_ERROR;

            return false;
        }

        Db::startTrans();
        try
        {
            //删除角色
            $role->Del($role_id);

            //删除角色-权限映射
            $rr = RoleRules::instance();
            $rr->DelByRoles([$role_id]);

            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * 角色信息
     *
     * @param string $role_id
     *
     * @return mixed
     */
    public function GetInfo(string $role_id)
    {
        $role = Role::instance();

        $info = $role->GetInfo($role_id);
        if (!$info)
        {
            static::$_error_code = \EC::ROLE_NOTEXIST_ERROR;

            return false;
        }

        unset($info['user_name']);

        return $info;
    }

    /**
     * 角色列表
     *
     * @param int|null $role_type
     * @param int      $page
     * @param int      $page_size
     *
     * @return array
     */
    public function GetList(int $role_type = null, string $order_field = null, int $is_asc = YES, int $page = DEF_PAGE, int $page_size = DEF_PAGE_SIZE)
    {
        $role = Role::instance();

        $list = $role->GetList($role_type, $order_field, $is_asc, $page, $page_size);

        return $list;
    }

    //----------------------------------------------- protected -------------------------------------------------------//

    /**
     * 检测角色下是否存在用户
     *
     * @param string $role_id
     *
     * @return bool
     */
    protected function CheckUserExist(string $role_id)
    {
        $ur = UserRoles::instance();

        return $ur->CheckUserExist($role_id);
    }

    /**
     * 检测角色是否存在
     *
     * @param string $roleId
     *
     * @return bool
     */
    protected function CheckRoleExist(string $roleId)
    {
        $role = Role::instance();

        if (!$role->CheckExist([$roleId]))
        {
            return false;
        }

        return true;
    }
}