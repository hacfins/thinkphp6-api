<?php

namespace app\api\model\log;

use app\api\model\Base;
use app\common\traits\Instance;

use app\common\facade\
{
    Device, Os
};

/*
 * 用户操作日志表
 *
 * 1、缓存:
 *     key: op_id
 */

class Logs extends Base
{
    use Instance;

    protected $_lk = 'op_id';

    //只读字段
    protected $readonly = ['op_id'];

    //自动时间
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime         = false;

    // system's human friendly name like Windows XP, MacOS 10.
    protected function setOsNameAttr()
    {
        return Os::getName() . ' ' . Os::getVersion();
    }

    // system's vendor like Linux, Windows, MacOS.
    protected function setOsFamilyAttr()
    {
        return Os::getName();
    }

    // system's human friendly version like XP, Vista, 10.
    protected function setOsVersionAttr()
    {
        return Os::getVersion();
    }

    //Device's brand name like iPad, iPhone, Nexus.
    protected function setDeviceModelAttr()
    {
        return Device::getName();
    }

    //ip -> city like jinan
    protected function setCityAttr()
    {
        $ipInfo = get_ip_info();

        return $ipInfo ? $ipInfo['city'] : '';
    }

    protected function setIpAttr()
    {
        return ip2long(request()->ip());
    }

    // +--------------------------------------------------------------------------
    // |  基本操作
    // +--------------------------------------------------------------------------
    /**
     * 添加用户操作记录
     *
     * @param string $opId
     * @param        $userName
     * @param int    $op_type
     * @param string $op
     * @param string $url
     * @param string $params
     * @param int    $opResult
     * @param string $opCommnt
     * @param int    $time
     * @param int    $userIO
     * @param string $useMem
     *
     * @return bool|string
     */
    public function Add(
        string $opId,
        $userName, int $op_type = LOGOP_OP_TYPE_ADD, string $op = '', string $url = '', string $params = '',
        int $opResult = 0, string $opCommnt = '', int $time = 0, int $userIO = 0, string $useMem = '')
    {
        if (isset($userName))
        {
            $rtn = self::create([
                    'op_id'      => $opId,
                    'user_name'  => $userName,
                    'op_type'    => $op_type,
                    'op'         => $op,
                    'op_url'     => $url,
                    'op_params'  => $params,
                    'op_result'  => $opResult,
                    'op_comment' => $opCommnt,
                    'use_time'   => $time,
                    'use_io'     => $userIO,
                    'use_mem'    => $useMem,

                    'os_name'      => '',
                    'os_family'    => '',
                    'os_version'   => '',
                    'device_model' => '',
                    'city'         => '',
                    'ip'           => 0,

                ]);

            return $rtn ? $opId : false;
        }

        return true;
    }

    /**
     * @param $op_id
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetInfo($op_id, bool $withTrashed = false)
    {
        $s = $this->field([
            'op_id',
            'user_name',
            'op_type',
            'op',
            'op_url',
            'op_params',
            'use_time',
            'use_io',
            'use_mem',
            'op_result',
            'op_comment',
            'os_name',
            'os_family',
            'os_version',
            'device_model',
            'city',
            'ip',
            'create_time'
        ])
            ->cache($this->Cache_Key($op_id), CACHE_TIME_SQL)
            ->where(['op_id' => $op_id])
            ->find();

        if (!$s)
        {
            return [];
        }

        return $s->toArray();
    }

    /**
     * 操作日志列表
     *
     * @param int|null $st 起始时间
     * @param int|null $et 截止时间
     * @param int      $page
     * @param int      $pageSize
     *
     * @return array [列表,总数]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetList($userKey = null, int $op_type = null, string $st = null, string $et = null, string $fullNameKey = null,
        string $orderField = null, int $isASC = YES, int $page = DEF_PAGE, int $pageSize = DEF_PAGE_SIZE)
    {
        $map  = [];
        $join = [];
        $order = [];

        if (isset($orderField))
        {
            $sortType = $isASC == YES ? 'ASC' : 'DESC';
            if ($orderField == 'create_time')
            {
                $order['lg.id']          = $sortType;
            }
        }

        if (isset($userKey))
        {
            if (is_string($userKey))
            {
                //对 '_' 进行转义
                $userKey = str_replace('_', '\_', $userKey);

                $map[] = [
                    'lg.user_name',
                    'like',
                    "%{$userKey}%"
                ];
            }
        }
        if (isset($op_type))
        {
            $map[] = ['lg.op_type', '=', $op_type];
        }
        if (isset($st) || isset($et))
        {
            sql_map_region($map, 'lg.create_time', $st, $et);
        }

        if (isset($fullNameKey))
        {
            $join[] = [
                'user u', 'u.user_name = lg.user_name', 'left'
            ];

            //对 '_' 进行转义
            $fullNameKey = str_replace('_', '\_', $fullNameKey);

            $map[] = [
                'u.full_name',
                'like',
                "%{$fullNameKey}%"
            ];
        }

        $count = $this->where($map)
            ->alias('lg')
            ->joins($join)
            ->count("DISTINCT lg.op_id");

        if ($count == 0)
        {
            $list = [];
        }
        else
        {
            $list = $this->distinct(true)
                ->field([
                    'lg.op_id'
                ])
                ->where($map)
                ->alias('lg')
                ->joins($join)
                ->page($page, $pageSize)
                ->order($order)
                ->select();

            $list = $list->toArray();
            foreach ($list as &$item)
            {
                $item = $this->GetInfo($item['op_id']);
            }
        }

        return [
            $list,
            $count,
        ];
    }
}