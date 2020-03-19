<?php

namespace app\api\model\rbac;

use app\api\model\Base;
use app\common\traits\Instance;

/*
 * 角色信息表
 *
 * 1、缓存:
 *     key: role_id
 */

class Role extends Base
{
    protected $_lk = 'role_id';

    use Instance;

    //只读字段
    protected $readonly = ['user_name'];

    //自动时间
    protected $autoWriteTimestamp = 'datetime';

    /**
     * 添加
     *
     * @param string $user_name   操作用户
     * @param string $role_name   角色名称
     * @param string $role_type   角色类型（普通角色、系统角色等）
     * @param string $description 描述信息
     *
     * @return bool|string
     */
    public function Add(string $user_name, string $role_name, int $role_type = ROLE_TYPE_GENERAL, string $description = '')
    {
        $roleId = guid();

        $rtn = self::create([
                'role_id'     => $roleId,
                'user_name'   => $user_name,
                'role_name'   => $role_name,
                'role_type'   => $role_type,
                'sort'        => DEF_SORT_NUM,
                'description' => $description,
            ]);

        if (!$rtn)
            return false;

        //修改排序值 == id号
        $rtn->Db_Update($roleId,['role_id' => $roleId],['sort' => $rtn->id]);

        return $rtn ? $roleId : false;
    }

    /**
     * 修改
     *
     * @param string      $roleId      角色Id号
     * @param string|null $role_name   角色名称
     * @param int|null    $role_type   角色类型（普通角色、系统角色等）
     * @param int|null    $sort        排序值
     * @param string|null $description 描述信息
     *
     * @return int|string
     */
    public function Modify(string $roleId, string $role_name = null, int $role_type = null, int $sort = null, string $description = null)
    {
        $data = [];

        if (isset($role_name))
        {
            $data['role_name'] = $role_name;
        }
        if (isset($role_type))
        {
            $data['role_type'] = $role_type;
        }
        if (isset($sort))
        {
            $data['sort'] = $sort;
        }
        if (isset($description))
        {
            $data['description'] = $description;
        }

        return $this->Db_Update($roleId, ['role_id' => $roleId], $data);
    }

    /**
     * 软删除
     *
     * @param string $roleId 角色Id号
     *
     * @return int
     */
    public function Del(string $roleId)
    {
        //同步缓存
        $this->Cache_Rm($roleId);

        return self::destroy(['role_id' => $roleId]);
    }

    /**
     * 获取信息
     *
     * @param string $roleId 角色Id号
     *
     * @return array
     */
    public function GetInfo($roleId,bool $withTrashed = false)
    {
        try
        {
            $field = [
                'user_name',
                'role_name',
                'role_type',
                'sort',
                'description',
            ];

            $info = $this->field($field)
                ->cache($this->Cache_Key($roleId), CACHE_TIME_SQL)
                ->where([
                    'role_id' => $roleId,
                ])
                ->find();

            if (!$info)
            {
                return [];
            }
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }

        return $info->toArray();
    }

    /**
     * 检测角色名称是否存在
     *
     * @param string      $role_name    角色名
     * @param string|null $exceptRoleId 排除的角色Id号
     * @param bool        $withTrashed  是否包含删除数据
     *
     * @return mixed role_id | false
     */
    public function CheckNameExist(string $role_name, string $exceptRoleId = null, bool $withTrashed = false)
    {
        $map[] = ['role_name', '=', $role_name];

        if (isset($exceptRoleId))
        {
            $map[] = [
                'role_id',
                '<>',
                $exceptRoleId,
            ];
        }

        if ($withTrashed)
        {
            return $this->withTrashed()
                ->where($map)
                ->value('role_id', false);
        }
        else
        {
            return $this->where($map)
                ->value('role_id', false);
        }
    }

    /**
     * 判断角色 Id 集合是否存在
     *
     * @param array $roleIds 角色ID集
     *
     * @return bool
     */
    public function CheckExist(array $roleIds)
    {
        $roleIds = array_unique($roleIds);

        foreach ($roleIds as $roleId)
        {
            $info = $this->GetInfo($roleId);
            if (!$info)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * 角色列表
     *
     * @param int|null $role_type
     * @param int      $page
     * @param int      $pageSize
     *
     * @return array
     */
    public function GetList(int $role_type = null, string $orderField = null, int $isASC = YES,
        int $page = DEF_PAGE, int $pageSize = DEF_PAGE_SIZE)
    {
        try
        {
            $map   = [];
            $order = [];

            if (isset($role_type))
            {
                $map[] = ['role_type', '=', $role_type];
            }

            //2.0 排序
            if (isset($orderField))
            {
                $sortType = $isASC == YES ? 'ASC' : 'DESC';

                if($orderField == 'sort')
                {
                    $order['sort'] = $sortType;
                }
            }

            $count = $this->where($map)
                ->count();

            if ($count > 0)
            {
                $list = $this->field(['role_id'])
                    ->where($map)
                    ->order($order)
                    ->page($page, $pageSize)
                    ->select();

                $list = $list->toArray();

                //角色信息
                foreach ($list as &$item)
                {
                    $info = $this->GetInfo($item['role_id']);

                    unset($info['user_name']);
                    unset($info['description']);

                    $item = array_merge($info, $item);
                }
            }
            else
            {
                $list = [];
            }
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }

        return [
            $list,
            $count,
        ];
    }
}