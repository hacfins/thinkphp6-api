<?php

namespace app\api\model\rbac;

use app\api\model\Base;
use app\common\traits\Instance;

/*
 * 权限信息表
 *
 * 1、缓存:
 *     key: rule_id
 */

class Rule extends Base
{
    protected $_lk = 'rule_id';

    use Instance;

    //只读字段
    protected $readonly = ['user_name'];

    //自动时间
    protected $autoWriteTimestamp = 'datetime';

    /**
     * 保存多个数据到当前数据对象
     *
     * @param $data
     *    'rule_id'
     *    'user_name'
     *    'module'
     *    'control'
     *    'method'
     *
     * @return \think\Collection
     */
    public function AddAll($data)
    {
        try
        {
            return $this->saveAll($data);
        }
        catch (\Throwable $e)
        {

        }
    }

    /**
     * DelByRuleIds
     *
     * @param array $rule_ids
     *
     * @return int
     */
    public function DelByRuleIds(array $rule_ids)
    {
        //同步缓存
        foreach ($rule_ids as $rule_id)
        {
            $this->Cache_Rm($rule_id);
        }

        return $this->destroy(function ($query) use ($rule_ids) {
            $query->where('rule_id', 'IN', $rule_ids);
        });
    }

    /**
     * 信息
     *
     * @param string $rule_id
     *
     * @return array|bool
     */
    public function GetInfo($rule_id, bool $withTrashed = false)
    {
        try
        {
            $s = $this->field([
                'user_name',
                'module',
                'control',
                'method',
            ])
                ->cache($this->Cache_Key($rule_id), CACHE_TIME_SQL)
                ->where([
                    'rule_id' => $rule_id,
                ])
                ->find();

            if (!$s)
            {
                return [];
            }
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }

        return $s->toArray();
    }

    /**
     * 获取列表
     *
     * @param array|null $module
     *
     * @return array
     */
    public function GetList($module = null, string $orderField = null, int $isASC = YES)
    {
        try
        {
            $map   = [];
            $order = [];
            if (isset($module)) //api-auth.role
            {
                $moduleContrls = ids2array($module, '-');

                $map[] = ['module', '=', $moduleContrls[0]];
                $map[] = ['control', '=', $moduleContrls[1]];
            }

            //2.0 排序
            if (isset($orderField))
            {
                $sortType = $isASC == YES ? 'ASC' : 'DESC';

                if ($orderField == 'module')
                {
                    $order['module']  = $sortType;
                    $order['control'] = $sortType;
                    $order['id']      = $sortType;
                }
            }

            $list = $this->field([
                'rule_id'
            ])
                ->where($map)
                ->order($order)
                ->select();

            if (!$list)
            {
                return [];
            }

            $list = $list->toArray();

            //rule 信息
            foreach ($list as &$item)
            {
                $info = $this->GetInfo($item['rule_id']);
                unset($info['user_name']);

                $item = array_merge($item, $info);
            }
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }

        return $list;
    }

    /**
     * 获取可视化的规则列表
     *
     * @param array|null $rolesArr
     *
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetViewRules(array $rolesArr = null)
    {
        $map[] = [
            'r.method', 'in', ['index', 'index_user']
        ];

        if (isset($rolesArr))
        {
            $map[] = ['rr.role_id', 'IN', $rolesArr];

            $map[] = [
                'rr.delete_time',
                'NULL',
                null
            ];
        }

        $list = $this->alias('r')
            ->distinct('r.control')
            ->field('r.rule_id')
            ->join('role_rules rr', 'rr.rule_id = r.rule_id', 'LEFT')
            ->where($map)
            ->select();

        if (!$list)
        {
            return [];
        }

        $list = $list->toArray();

        //rule 信息
        foreach ($list as &$item)
        {
            $info = $this->GetInfo($item['rule_id']);

            $item = [
                'c' => $info['module'] . '-' . $info['control'],
                'm' => $info['method']
            ];
        }

        return $list;
    }
}