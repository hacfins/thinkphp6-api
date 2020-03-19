<?php
namespace app\api\model\location;

use app\api\model\Base;
use app\common\traits\Instance;

/*
 * 市
 *
 * 1、缓存:
 *     key: province_code
 */
class AreaCity extends Base
{
    protected $pk  = 'code';
    protected $lk  = 'code';

    use Instance;

    // +--------------------------------------------------------------------------
    // |  基本操作
    // +--------------------------------------------------------------------------
    public function GetInfo($city_code, bool $withTrashed = false)
    {
        $list = $this->field([
            'name',
            'province_code'
        ])
            ->cache($this->Cache_Key($city_code), CACHE_TIME_SQL_DAY)
            ->where('code', '=', $city_code)
            ->find();

        if (!$list)
        {
            return [];
        }

        return $list->toArray();

    }

    /**
     * 市列表
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetList(int $province_code)
    {
        $list = $this->field([
            'code',
            'name'
        ])
            ->cache($this->Cache_Key('p' . $province_code), CACHE_TIME_SQL_DAY)
            ->where('province_code', '=', $province_code)
            ->order([
                'code' => 'asc',
            ])
            ->select();

        return $list->toArray();
    }
}