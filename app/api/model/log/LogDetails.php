<?php

namespace app\api\model\log;

use app\api\model\Base;
use app\common\traits\Instance;

/*
 * 用户操作日志详情表
 *
 * 1、缓存:
 *     key: opd_id
 */

class LogDetails extends Base
{
    use Instance;

    protected $_lk = 'opd_id';

    //自动时间
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime         = false;

    // +--------------------------------------------------------------------------
    // |  基本操作
    // +--------------------------------------------------------------------------
    /**
     * 添加记录
     *
     * @param string $opdTable 表名称
     * @param string $opdKey   表的逻辑Id号
     * @param string $opdDiff  修改详细说明
     *
     * @return bool|string
     */
    public static function Add(string $opdTable = '', string $opdKey = '', string $opdDiff = '')
    {
        $opId  = $GLOBALS['g_logs_opid'];
        $opdId = guid();

        $rtn = self::create([
            'opd_id'    => $opdId,
            'op_id'     => $opId,
            'opd_table' => $opdTable,
            'opd_key'   => $opdKey,
            'opd_diff'  => $opdDiff
        ]);

        return $rtn ? $opdId : false;
    }

    /**
     * 获取信息
     *
     * @param mixed $opd_id
     * @param bool  $withTrashed
     *
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetInfo($opd_id, bool $withTrashed = false)
    {
        $s = $this->field([
            'opd_id',
            'op_id',
            'opd_table',
            'opd_key',
            'opd_diff',
            'create_time'
        ])
            ->cache($this->Cache_Key($opd_id), CACHE_TIME_SQL)
            ->where(['opd_id' => $opd_id])
            ->find();

        if (!$s)
        {
            return [];
        }

        return $s->toArray();
    }

    public function GetDiff($opd_id, bool $withTrashed = false)
    {
        $info = $this->GetInfo($opd_id, $withTrashed);
        return $info['opd_diff'] ?? '';
    }

    /**
     * @param string $opId
     *
     * @return array
     */
    public function GetOpdIds(string $opId)
    {
        return $this->distinct(true)
            ->where(['op_id' => $opId])
            ->column('opd_id');
    }
}