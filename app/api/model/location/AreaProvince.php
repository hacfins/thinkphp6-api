<?php
namespace app\api\model\location;

use app\api\model\Base;
use app\common\traits\Instance;

/*
 * 省
 *
 * 1、缓存:
 *     key: 整表缓存
 */
class AreaProvince extends Base
{
    protected $pk = 'code';
    protected $lk = 'code';

    use Instance;

    // +--------------------------------------------------------------------------
    // |  基本操作
    // +--------------------------------------------------------------------------
    public function GetInfo($code, bool $withTrashed = false)
    {
        $list = $this->field([
            'name'
        ])
            ->cache($this->Cache_Key($code), CACHE_TIME_SQL_DAY)
            ->where('code', '=', $code)
            ->find();

        if (!$list)
        {
            return [];
        }

        return $list->toArray();
    }

    /**
     * 省列表
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \\think\db\exception\ModelNotFoundException
     */
    public function GetList()
    {
        $list = $this->field([
            'code',
            'name'
        ])
            ->cache($this->Cache_Key('areaprovince'), CACHE_TIME_SQL_DAY) //整表缓存
            ->order([
                'code' => 'asc',
            ])
            ->select();

        return $list->toArray();
    }
}