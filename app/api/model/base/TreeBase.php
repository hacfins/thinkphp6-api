<?php

namespace app\api\model\base;

use app\api\model\Base;
use think\facade\Db;

/*
 * 分类信息表
 *
 * 1、缓存:
 *     key: cc_id
 */

class TreeBase extends Base
{
    protected $_cc_id;
    protected $_cc_name;
    protected $_cc_path;
    protected $_cc_exDataKeys = null;

    //只读字段
    protected $readonly = ['user_name'];

    //自动时间
    protected $autoWriteTimestamp = 'datetime';

    //自动完成
    protected $insert = ['user_name'];
    protected $update = [];

    protected function setUserNameAttr($value)
    {
        return strtolower($value);
    }

    /**
     * 架构函数
     *
     * @access public
     *
     * @param  array|object $data 数据
     */
    public function __construct($data = [])
    {
        parent::__construct($data);

        $this->pk = $this->_cc_id;
        $this->_lk = $this->_cc_id;
    }

    // +--------------------------------------------------------------------------
    // |  基本操作
    // +--------------------------------------------------------------------------
    /**
     * 添加课堂分类
     *
     * @param string      $user_name 用户名
     * @param string      $cc_name   分类名称
     * @param string|null $cc_path   父组的路径
     * @param int         $pid       父级ID
     * @param array       $exDatas   其他数据（例如  ['school_id' => 'xxx']）
     *
     * @return false|int
     */
    public function Add(string $user_name, string $cc_name = '', string $cc_path = null, int $pid = 0, array $exDatas=null)
    {
        $level = substr_count($cc_path, '/');
        $data  = [
            'user_name'     => $user_name,
            $this->_cc_name => $cc_name,
            $this->_cc_path => $cc_path,
            'level'         => $level,
            'parent_id'     => $pid
        ];

        if($this->_cc_exDataKeys && $exDatas)
        {
            foreach ($this->_cc_exDataKeys as $exKey)
            {
                $data[$exKey] = $exDatas[$exKey];
            }
        }

        $obj = self::create($data);
        if ($obj)
        {
            $cc_id   = $obj->{$this->_cc_id};
            $cc_path = $cc_path . $cc_id . '/';

            $rtn = $this->Db_Update($cc_id, [$this->_cc_id => $cc_id], [
                $this->_cc_path => $cc_path, 'sort' => $cc_id ]);

            return $rtn ? $cc_id : false;
        }
    }

    /**
     * 修改课堂分类
     *
     * @param int         $cc_id
     * @param string|null $cc_name
     * @param int|null    $sort
     *
     * @return int|string
     */
    public function Modify(int $cc_id, string $cc_name = null, int $sort = null)
    {
        $data = [];

        if (isset($cc_name))
        {
            $data[$this->_cc_name] = $cc_name;
        }
        if (isset($sort))
        {
            $data['sort'] = $sort;
        }

        //同步缓存
        $this->Cache_Rm($cc_id . CACHE_WITHTRASHED);
        return $this->Db_Update($cc_id, [$this->_cc_id => $cc_id], $data);
    }

    /**
     * 软删除
     *
     * @param int $cc_id 分组id
     *
     * @return int
     */
    public function Del(int $cc_id)
    {
        //同步缓存
        $this->Cache_Rm($cc_id);
        $this->Cache_Rm($cc_id . CACHE_WITHTRASHED);

        $ccIdKey = $this->_cc_id;

        return self::destroy(function ($query) use ($ccIdKey, $cc_id) {
            $query->where($ccIdKey, '=', $cc_id);
        });
    }

    /**
     * 获取课堂分类信息
     *
     * @param int  $cc_id       课堂分类id
     * @param bool $withTrashed 是否包含删除数据
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetInfo($cc_id, bool $withTrashed = false)
    {
        $field = [
            $this->_cc_id,
            'user_name',
            $this->_cc_name,
            $this->_cc_path,
            'sort',
            'level',
            'parent_id',
            'create_time'
        ];

        if($this->_cc_exDataKeys)
        {
            foreach ($this->_cc_exDataKeys as $exKey)
            {
                $field[] = $exKey;
            }
        }

        if ($withTrashed)
        {
            $user = self::withTrashed()->field($field)
                ->cache($this->Cache_Key($cc_id . CACHE_WITHTRASHED), CACHE_TIME_SQL)
                ->where([$this->_cc_id => $cc_id])
                ->find();
        }
        else
        {
            $user = $this->field($field)
                ->cache($this->Cache_Key($cc_id), CACHE_TIME_SQL)
                ->where([$this->_cc_id => $cc_id])
                ->find();
        }

        if (!$user)
        {
            return [];
        }

        $info = $user->toArray();

        return $info;
    }

    /**
     * 获取课堂分类路径
     *
     * @param int $cc_id
     *
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetCclassPath(int $cc_id)
    {
        $info = $this->GetInfo($cc_id, true);

        return $info[$this->_cc_path] ?? false;
    }

    /**
     * 获取课堂分类名称
     *
     * @param int $cc_id 课堂分类id
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetCclassName(int $cc_id)
    {
        $info = $this->GetInfo($cc_id, true);

        return $info[$this->_cc_name] ?? '';
    }

    /**
     * 根据分组名称，获取分组的Id号
     *
     * @param string $ccName
     * @param int    $parentId
     *
     * @return mixed
     */
    public function GetCCIdByName(string $ccName, int $parentId = 0, array $exDatas=null)
    {
        $map = [
            ['parent_id', '=', $parentId],
            [$this->_cc_name, '=', $ccName]
        ];
        if (isset($exDatas))
        {
            foreach ($exDatas as $key => $item)
            {
                $map[] = [
                    $key, '=', $item
                ];
            }
        }

        return $this->where($map)
            ->value($this->_cc_id, false);
    }

    /**
     * @param int|null    $pid
     * @param int         $level
     * @param int         $self
     * @param null        $path
     * @param bool        $all
     * @param array|null  $orIdArr
     * @param $
     * @param array|null  $exDatas
     * @param string|null $orderField
     * @param int         $isASC
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function GetList(int $pid = null, int $level = LEVEL_ONE, int $self = 1, $path = null,
        bool $all = false, array $orIdArr = null, array $exDatas=null, string $orderField = null, int $isASC = YES)
    {
        $map = [];
        $order = [];

        //是否是所有的
        if (!$all)
        {
            $map[] = [
                'p.delete_time',
                'NULL',
                null,
            ];
        }

        //父级Id号 + 几级
        if (isset($pid) && $level == LEVEL_ONE)
        {
            $map[] = [
                'p.parent_id',
                '=',
                $pid
            ];
        }
        else
        {
            $map[] = [
                'p.level',
                'between',
                [$self + 1, $self + $level]
            ];

            if (isset($path))
            {
                if (is_string($path))
                {
                    $map[] = [
                        'p.' . $this->_cc_path,
                        'like',
                        "$path%"
                    ];
                }
                else
                {
                    $bFirst  = true;
                    $rawPath = '0 AND (';
                    foreach ($path as $item)
                    {
                        if (!$bFirst)
                        {
                            $rawPath .= ' or ';
                        }

                        $rawPath .= "p." . $this->_cc_path . " like '$item%'";
                        $bFirst  = false;
                    }
                    $rawPath .= ')';

                    $map[] = [
                        'p.level',
                        '>',
                        Db::raw($rawPath)
                    ];
                }
            }
        }

        //额外条件
        if (isset($exDatas))
        {
            foreach ($exDatas as $key => $item)
            {
                $map[] = [
                    $key, '=', $item
                ];
            }
        }

        //满足条件的其他 Id号列表
        if (isset($orIdArr) && $orIdArr)
        {
            if (!$all)
            {
                $map1[] = [
                    'p.delete_time',
                    'NULL',
                    null,
                ];
            }

            $map1[] = [
                'p.level',
                'between',
                [$self + 1, $self + $level]
            ];

            $map1[] = [
                'p.' . $this->_cc_id,
                'IN',
                $orIdArr,
            ];
        }

        if (isset($orderField))
        {
            $sortType = $isASC == YES ? 'ASC' : 'DESC';
            if ($orderField == 'sort')
            {
                $order['p.sort'] = $sortType;
            }
        }

        $mapWhere = isset($map1) ? [$map, $map1] : [$map];

        $count = $this->withTrashed()
            ->alias('p')
            ->whereOr($mapWhere)
            ->count();

        if (0 == $count)
        {
            $list = [
            ];
        }
        else
        {
            $list = $this->withTrashed()
                ->field([
                    'p.' . $this->_cc_id
                ])
                ->alias('p')
                ->whereOr($mapWhere)
                ->order($order)
                ->select();

            if (!$list)
                $list = [];
            else
            {
                $list = $list->toArray();

                //课堂分类信息
                foreach ($list as &$item)
                {
                    $info = $this->GetInfo($item[$this->_cc_id], true);
                    $item = array_merge($item, $info);
                }
            }
        }

        return [
            $list,
            $count,
        ];
    }

    /**
     * 检查分类是否存在
     *
     * @param string      $cc_name
     * @param int         $pid
     * @param int|null    $cc_id
     * @param string|null $exceptName
     * @param bool        $withTrashed
     *
     * @return bool
     */
    public function CheckExist(string $cc_name, int $pid, int $cc_id = null, array $exDatas=null,
        string $exceptName = null, bool $withTrashed = false)
    {
        $map = [];

        if (isset($exceptName))
        {
            $map[] = [
                $this->_cc_name,
                '<>',
                $exceptName,
            ];
        }

        if (isset($cc_id))
        {
            $map[] = [
                $this->_cc_id,
                '<>',
                $cc_id
            ];
        }

        if (isset($cc_name))
        {
            $map[] = [
                $this->_cc_name,
                '=',
                strtolower($cc_name)
            ];
        }

        if (isset($pid))
        {
            $map[] = [
                'parent_id',
                '=',
                $pid
            ];
        }

        //额外条件
        if (isset($exDatas))
        {
            foreach ($exDatas as $key => $item)
            {
                $map[] = [
                    $key, '=', $item
                ];
            }
        }

        if ($withTrashed)
        {
            return $this->withTrashed()
                ->where($map)
                ->value($this->_cc_id) ? true : false;
        }
        else
        {
            return $this->where($map)
                ->value($this->_cc_id) ? true : false;
        }
    }
}