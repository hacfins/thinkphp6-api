<?php

namespace app\api\model\rbac;

use app\api\model\Base;
use Carbon\Carbon;
use app\common\traits\Instance;

/*
 * 角色权限映射信息表
 *
 * 1、缓存:
 *     key: 未用缓存
 */

class RoleRules extends Base
{
    protected $_lk = 'rr_id';

    use Instance;

    //只读字段
    protected $readonly = ['user_name'];

    //自动时间
    protected $autoWriteTimestamp = 'datetime';

    /**
     * ModifyByRules
     *
     * @param string      $user_name 操作用户
     * @param string      $roleId    角色id号
     * @param array       $ruleIds   权限列表
     * @param string|null $module    模块名称
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function ModifyByRules(string $user_name, string $roleId, array $ruleIds, string $module = null)
    {
        $map[] = ['rr.role_id', '=', $roleId];
        if (isset($module))
        {
            $map[] = ['r.module', '=', $module];
        }

        // 原始数据列表
        $oldList = $this->alias('rr')
            ->where($map)
            ->join('rule r', 'rr.rule_id=r.rule_id', 'left')
            ->column('rr.rule_id');

        //1.0 删除的
        $toDels = array_diff($oldList, $ruleIds);
        if ($toDels)
        {
            self::destroy(function ($query) use ($roleId, $toDels) {
                $map[] = [
                    'role_id',
                    '=',
                    $roleId
                ];
                $map[] = [
                    'rule_id',
                    'in',
                    $toDels
                ];

                $query->where($map);
            });
        }

        //2.0 新增&修改
        $toAdds = array_diff($ruleIds, $oldList);
        if ($toAdds)
        {
            foreach ($toAdds as $nv)
            {
                self::create([
                        'rr_id'       => guid(),
                        'user_name'   => $user_name,
                        'role_id'     => $roleId,
                        'rule_id'     => $nv,
                        'update_time' => Carbon::now()->toDateTimeString(),
                        'create_time' => Carbon::now()->toDateTimeString()
                    ]);
            }
        }
    }

    /**
     * DelByRole
     *
     * @param array $roleIds
     *
     * @return int
     */
    public function DelByRoles(array $roleIds)
    {
        return self::destroy(function ($query) use ($roleIds) {
            $query->where('role_id', 'IN', $roleIds);
        });
    }

    /**
     * DelByRule
     *
     * @param array $ruleIds
     *
     * @return int
     */
    public function DelByRules(array $ruleIds)
    {
        return self::destroy(function ($query) use ($ruleIds) {
            $query->where('rule_id', 'IN', $ruleIds);
        });
    }

    /**
     * GetRulesByRoles
     *
     * @param array $roleIds
     *
     * @return array
     */
    public function GetRulesByRoles(array $roleIds)
    {
        return $this->distinct(true)
            ->where('role_id', 'IN', $roleIds)
            ->order('id')
            ->column('rule_id');
    }
}