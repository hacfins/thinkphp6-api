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

class UserLogs extends Base
{
    protected $_lk = 'op_id';

    use Instance;

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
     * @param int    $op_type 操作类型
     * @param string $tb_id   表的业务逻辑Id号
     * @param string $tb_name 表名称
     */
    public function Add($userName, int $op_type = USERLOGOP_OP_TYPE_LOGIN, string $description = '', string $tb_name = '', string $tb_id = '')
    {
        if (isset($userName))
        {
            self::create([
                'op_id'       => guid(),
                'user_name'   => $userName,
                'op_type'     => $op_type,
                'tb_id'       => $tb_id,
                'tb_name'     => $tb_name,
                'description' => $description,

                'os_name'      => '',
                'os_family'    => '',
                'os_version'   => '',
                'device_model' => '',
                'city'         => '',
                'ip'           => 0,
            ]);
        }
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
            'user_name',
            'op_type',
            'description',
            'tb_id',
            'tb_name',
            'os_name',
            'os_family',
            'os_version',
            'device_model',
            'city',
            'ip',
            'create_time',
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
     * 根据表名+逻辑id号，获取最新记录
     *
     * @param string $tbName
     * @param string $logicId
     *
     * @return mixed
     */
    public function GetOpId(string $tbName, string $logicId)
    {
        return $this->field('op_id')
            ->where([
                ['tb_name', '=', $tbName],
                ['tb_id', '=', $logicId],
            ])
            ->order(['id' => 'desc'])
            ->value('op_id', false);
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
    public function GetList(
        string $user_name = null, int $op_type = null, string $st = null, string $et = null, string $orderField = null, int $isASC = YES,
        int $page = DEF_PAGE, int $pageSize = DEF_PAGE_SIZE)
    {
        $map   = [];
        $order = [];

        if (isset($user_name))
        {
            $map[] = ['user_name', '=', $user_name];
        }
        if (isset($op_type))
        {
            $map[] = ['op_type', '=', $op_type];
        }
        if (isset($st) || isset($et))
        {
            sql_map_region($map, 'create_time', $st, $et);
        }
        if (isset($orderField))
        {
            $sortType = $isASC == YES ? 'ASC' : 'DESC';
            if ($orderField == 'create_time')
            {
                $order['id'] = $sortType;
            }
        }

        $count = $this->where($map)
            ->count();

        if ($count == 0)
        {
            $list = [];
        }
        else
        {
            $list = $this->field([
                'op_id',
            ])
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();

            $list = $list->toArray();
            foreach ($list as &$item)
            {
                $info = $this->GetInfo($item['op_id']);
                unset($info['tb_id']);
                unset($info['tb_name']);

                $item = $info;
            }
        }

        return [
            $list,
            $count,
        ];
    }

    /**
     * @param string      $st
     * @param string      $et
     * @param string|null $orderField
     * @param int         $isASC
     * @param int         $page
     * @param int         $page_size
     *
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function Statistic(string $st, string $et, string $orderField = null, int $isASC = YES, int $page = DEF_PAGE, int $page_size = DEF_PAGE_SIZE)
    {
        $order = [];
        $map[] = [
            'ul.op_type',
            '=',
            YES
        ];

        $map[] = [
            'ul.tb_name',
            '=',
            'user_tokens'
        ];

        sql_map_region($map, 'ul.create_time', $st, $et);

        $join[] = [
            'user u',
            'u.user_name = ul.user_name',
            'left'
        ];

        $count = $this->withTrashed()
            ->alias('ul')
            ->where($map)
            ->joins($join)
            ->group('u.company')
            ->count();

        if (isset($orderField))
        {
            $sortType = $isASC == YES ? 'ASC' : 'DESC';
            if ($orderField == 'count')
            {
                $order['count']          = $sortType;
                $order['ul.create_time'] = $sortType;
            }
        }

        $list = $this->field([
            'u.company',
            'count(*) as count'
        ])
            ->alias('ul')
            ->joins($join)
            ->where($map)
            ->page($page, $page_size)
            ->group('u.company')
            ->order($order)
            ->select();

        $list = $list->toArray();

        return [
            $list,
            $count,
        ];
    }
}