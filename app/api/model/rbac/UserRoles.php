<?php

namespace app\api\model\rbac;

use app\api\model\Base;
use app\common\traits\Instance;

/*
 * 用户角色映射信息表
 *
 * 1、缓存:
 *     key: 未缓存，后期考虑怎么调高性能
 */

class UserRoles extends Base
{
    protected $_lk = 'user_name';

    use Instance;

    //只读字段
    protected $readonly = ['user_name'];

    //自动时间
    protected $autoWriteTimestamp = 'datetime';

    /**
     * 用户添加角色
     *
     * @param string $user_name
     * @param string $role_id
     *
     * @return bool|false|int
     */
    public function Add(string $user_name, string $role_id)
    {
        $uro_id = guid();
        $rtn    = self::create([
                'uro_id'    => $uro_id,
                'user_name' => $user_name,
                'role_id'   => $role_id
            ]);

        return $rtn ? $rtn->uro_id : false;
    }

    /**
     * 修改某用户的角色
     *
     * @param string $userName 用户名
     * @param array  $roleIds  新角色ID集
     *
     * @return void
     */
    public function ModifyRoleByUser(string $userName, array $roleIds)
    {
        try
        {
            $oldRoleIds = $this->GetRoles($userName);

            //新增的角色
            $addRoleIdArr = array_diff($roleIds, $oldRoleIds);
            if ($addRoleIdArr)
            {
                $addRoleIds = [];
                foreach ($addRoleIdArr as $role_id)
                {
                    $addRoleIds[] = [
                        'uro_id'    => guid(),
                        'user_name' => $userName,
                        'role_id'   => $role_id,
                    ];
                }

                $this->saveAll($addRoleIds);
            }

            //删除的角色
            $delRoleIdArr = array_diff($oldRoleIds, $roleIds);
            if ($delRoleIdArr)
            {
                self::destroy(function ($query) use ($userName, $delRoleIdArr) {
                    $query->where([
                        ['user_name', '=', $userName],
                        ['role_id', 'IN', $delRoleIdArr]
                    ]);
                });
            }
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }
    }

    /**
     * DelByUsers
     *
     * @param string $userName
     *
     * @return void
     */
    public function DelByUsers(array $userNames)
    {
        self::destroy(function ($query) use ($userNames) {
            $query->where('user_name', 'IN', $userNames);
        });
    }

    /**
     * GetUsers
     *
     * @param string $roleId
     * @param bool   $withTrashed 是否包含删除数据
     *
     * @return bool
     */
    public function CheckUserExist(string $roleId, bool $withTrashed = false)
    {
        if ($withTrashed)
        {
            return $this->withTrashed()
                ->where(['role_id' => $roleId])
                ->value('user_name', false);
        }
        else
        {
            return $this->where(['role_id' => $roleId])
                ->value('user_name', false);
        }
    }

    /**
     * GetRoles
     *
     * @param string $userName
     *
     * @return array
     */
    public function GetRoles(string $userName = null)
    {
        if (is_null($userName))
        {
            return [ROLE_GUEST_ROLE];
        }

        return $this->distinct(true)
            ->where(['user_name' => $userName])
            ->order('id')
            ->column('role_id');
    }

    /**
     * 根据角色id号、关键字获取列表
     *
     * @param string|null $roleId
     * @param string|null $user_nameKey
     * @param string|null $nick_nameKey
     * @param int|null    $type
     * @param bool        $all
     * @param int         $page
     * @param int         $pageSize
     *
     * @return array 返回用户列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetList(
        string $roleId = null, string $user_nameKey = null, string $nick_nameKey = null, string $full_nameKey = null,
        int $type = null, bool $all = false, string $orderField = null, int $isASC = YES, $page = DEF_PAGE, $pageSize = DEF_PAGE_SIZE)
    {
        $map   = [];
        $join  = [];
        $order = [];

        if (isset($roleId))
        {
            $map[] = [
                'ur.role_id',
                '=',
                $roleId
            ];
        }

        if (isset($user_nameKey))
        {
            //对 '_' 进行转义
            $user_nameKey = str_replace('_', '\_', $user_nameKey);

            $map[] = [
                'u.user_name',
                'like',
                "%{$user_nameKey}%"
            ];
        }

        if (isset($nick_nameKey))
        {
            //对 '_' 进行转义
            $nick_nameKey = str_replace('_', '\_', $nick_nameKey);

            $map[] = [
                'u.nick_name',
                'like',
                "%{$nick_nameKey}%"
            ];
        }

        if (isset($full_nameKey))
        {
            //对 '_' 进行转义
            $full_nameKey = str_replace('_', '\_', $full_nameKey);

            $map[] = [
                'u.full_name',
                'like',
                "%{$full_nameKey}%"
            ];
        }

        if (isset($orderField))
        {
            $sortType = $isASC == YES ? 'ASC' : 'DESC';
            if ($orderField == 'create_time')
            {
                $order['u.id'] = $sortType;
            }
        }

        if (isset($type))
        {
            $map[] = [
                'u.type',
                '=',
                $type
            ];
        }

        if (!$all)
        {
            //查询字段是否（不）是Null
            //!!! 不是值
            $map[] = [
                'ur.delete_time',
                'NULL',
                null
            ];
        }

        if (isset($user_nameKey) || isset($nick_nameKey) || isset($full_nameKey) || isset($type) || !$all)
        {
            $join[] = [
                'user u',
                'ur.user_name = u.user_name',
                'right'
            ];
        }

        $count = $this->withTrashed()
            ->alias('ur')
            ->where($map)
            ->joins($join)
            ->count("DISTINCT u.user_name");

        if (0 == $count)
        {
            $list = [];
        }
        else
        {
            $list = $this->withTrashed()
                ->distinct(true)
                ->field([
                    'u.user_name'
                ])
                ->alias('ur')
                ->where($map)
                ->joins($join)
                ->page($page, $pageSize)
                ->order($order)
                ->select();

            if (!$list)
            {
                $list = [];
            }
            else
            {
                $list = $list->toArray();
            }
        }

        return [
            $list,
            $count,
        ];
    }
}