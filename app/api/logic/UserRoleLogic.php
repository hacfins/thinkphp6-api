<?php

namespace app\api\logic;

use app\api\model\
{
    rbac\Role, rbac\User, rbac\UserRoles
};
use think\facade\Db;

/**
 * 用户权限类
 */
class UserRoleLogic extends BaseLogic
{
    /**
     * 根据角色获取用户信息列表
     *
     * @param string|null $roleId
     * @param string|null $user_nameKey
     * @param string|null $nick_nameKey
     * @param int|null    $type 用户类型
     * @param bool        $all  是否包含被删除的用户
     * @param int         $page
     * @param int         $pageSize
     *
     * @return mixed
     */
    public function GetList(
        string $roleId = null, string $user_nameKey = null, string $nick_nameKey = null, string $full_nameKey = null,
        int $type = null, bool $all = false, string $order_field = null, int $is_asc = YES, $page = DEF_PAGE, $pageSize = DEF_PAGE_SIZE)
    {
        try
        {
            $userRoleCls = UserRoles::instance();
            $userRoles   = $userRoleCls->GetList($roleId, $user_nameKey, $nick_nameKey, $full_nameKey, $type, $all, $order_field, $is_asc,
                $page, $pageSize);

            if ($userRoles[1] > 0)
            {
                //获取机构信息、角色名称信息等
                $role = Role::instance();
                $user = User::instance();
                foreach ($userRoles[0] as $k => &$userRole)
                {
                    $userName = $userRole['user_name'];

                    //用户基本信息
                    $userInfo = $user->GetInfo($userName, true);
                    if ($userInfo)
                    {
                        $userRole['nick_name'] = $userInfo['nick_name'];
                        $userRole['avator']    = $userInfo['avator'];
                        $userRole['full_name'] = $userInfo['full_name'];
                     //   $userRole['user_type'] = $userInfo['type'];
                    }

                    //角色基本信息
                    $role_ids = $userRoleCls->GetRoles($userName);
                    if (!empty($role_ids))
                    {
                        foreach ($role_ids as $role_id)
                        {
                            $roleInfo = $role->GetInfo($role_id);
                            if ($roleInfo)
                            {
                                $userRole['role_info'][] = [
                                    'role_id'   => $role_id,
                                    'role_name' => $roleInfo['role_name'],
                                    'role_type' => $roleInfo['role_type']
                                ];
                            }
                        }
                    }
                }
            }

            return $userRoles;
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    /**
     * 修改用户角色
     *
     * @param string $userName 待修改的用户名
     * @param string $role_ids 角色列表
     *
     * @return bool
     */
    public function Modify(string $userName, string $role_ids)
    {
        $role_ids = ids2array($role_ids);

        if (empty($role_ids))
        {
            static::$_error_code = \EC::ROLE_NEED_ONE_ERROR;

            return false;
        }

        //检测角色id是否存在
        foreach ($role_ids as $role_id)
        {
            $info = Role::instance()->GetInfo($role_id);
            if (!$info)
            {
                static::$_error_code = \EC::ROLE_NOTEXIST_ERROR;

                return false;
            }
        }

        $oldRoleIds = UserRoles::instance()->GetRoles($userName);
        //删除的角色
        $delRoleIdArr = array_diff($oldRoleIds, $role_ids);
        //系统角色不能删除
        foreach ($delRoleIdArr as $role_id)
        {
            $info = Role::instance()->GetInfo($role_id);
            if ($info['role_type'] == ROLE_TYPE_SYSTEM)
            {
                static::$_error_code = \EC::ROLE_CANT_DELETE_ERROR;

                return false;
            }
        }

        $newRoleIdArr = array_diff($role_ids, $oldRoleIds);
        //系统角色不能添加
        foreach ($newRoleIdArr as $role_id)
        {
            $info = Role::instance()->GetInfo($role_id);
            if ($info['role_type'] == ROLE_TYPE_SYSTEM)
            {
                static::$_error_code = \EC::ROLE_CANT_ADD_ERROR;

                return false;
            }
        }

        Db::startTrans();
        try
        {
            //修改用户角色列表
            $userRoleCls = UserRoles::instance();
            $userRoleCls->ModifyRoleByUser($userName, $role_ids);

            Db::commit();
        }
        Catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        return true;
    }
}