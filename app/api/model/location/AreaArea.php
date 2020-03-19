<?php
namespace app\api\model\location;

use app\api\model\Base;
use app\common\traits\Instance;

/*
 * 区域
 *
 * 1、缓存:
 *     key: city_code
 */
class AreaArea extends Base
{
    protected $pk  = 'code';
    protected $_lk = 'code';

    use Instance;

    // +--------------------------------------------------------------------------
    // |  基本操作
    // +--------------------------------------------------------------------------
    public function GetInfo($code, bool $withTrashed = false)
    {
        $list = $this->field([
            'name',
            'city_code',
            'province_code'
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
     * 区列表
     * 
     * @param int $city_code
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function GetList(int $city_code)
    {
        $list = $this->field([
            'code',
            'name'
        ])
            ->cache($this->Cache_Key('areaprovince' . $city_code), CACHE_TIME_SQL_DAY)
            ->where('city_code', '=', $city_code)
            ->order([
                'code' => 'asc',
            ])
            ->select();

        return $list->toArray();
    }
}