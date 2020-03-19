<?php

namespace app\api\model\base;

use app\api\model\Base;

/*
 * 用户映射信息表
 *
 * 1、缓存:
 *     key: 未缓存，后期考虑怎么调高性能
 */

class MapsBase extends Base
{
    protected $_pkId = 'id';
    protected $_mapId;
    protected $_sorKey;
    protected $_desKey;

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

        $this->pk = $this->_pkId;
    }

    // +--------------------------------------------------------------------------
    // |  基本操作
    // +--------------------------------------------------------------------------
    /**
     * 用户添加映射
     *
     * @param string $user_name 用户名
     * @param string $sorkey    映射的源key
     * @param string $desKey    映射的目的key
     *
     * @return bool|false|int
     */
    public function Add(string $user_name, string $sorkey, string $desKey)
    {
        $mapId = guid();
        $data  = [
            $this->_mapId  => $mapId,
            'user_name'    => $user_name,
            $this->_desKey => $desKey
        ];

        if ($sorkey != 'user_name')
        {
            $data[$this->_sorKey] = $sorkey;
        }

        $rtn = self::create($data);

        return $rtn ? $mapId : false;
    }

    /**
     * 修改某用户的映射
     *
     * @param string $userName 用户名
     * @param string $sorkey   映射的源key
     * @param string $desKeys  映射的目的keys
     *
     * @return void
     */
    public function ModifyBySorKey(string $userName, string $sorkey, array $desKeys)
    {
        try
        {
            $oldDesKeys = $this->GetDesKeys($sorkey);

            //新增的映射
            $addDesKeysArr = array_diff($desKeys, $oldDesKeys);
            if ($addDesKeysArr)
            {
                $addDatas = [];
                foreach ($addDesKeysArr as $desKey)
                {
                    $data = [
                        $this->_mapId  => guid(),
                        'user_name'    => $userName,
                        $this->_desKey => $desKey,
                    ];
                    if ($sorkey != 'user_name')
                    {
                        $data[$this->_sorKey] = $sorkey;
                    }

                    $addDatas[] = $data;
                }

                $this->saveAll($addDatas);
            }

            //删除的映射
            $delDesKeysArr = array_diff($oldDesKeys, $desKeys);
            if ($delDesKeysArr)
            {
                self::destroy(function ($query) use ($sorkey, $delDesKeysArr) {
                    $query->where([
                        [$this->_sorKey, '=', $sorkey],
                        [$this->_desKey, 'IN', $delDesKeysArr]
                    ]);
                });
            }
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }
    }

    /**
     * DelBySorKeys
     *
     * @param string $userName
     *
     * @return void
     */
    public function DelBySorKeys(array $sorKeys, string $desKey = null)
    {
        self::destroy(function ($query) use ($sorKeys, $desKey) {
            $map[] = [
                $this->_sorKey, 'IN', $sorKeys
            ];
            if ($desKey)
            {
                $map[] = [
                    $this->_desKey, '=', $desKey
                ];
            }
            $query->where($map);
        });
    }

    /**
     * CheckKeyExist
     *
     * @param string $sorKey
     * @param string $desKey
     * @param bool   $withTrashed 是否包含删除数据
     *
     * @return bool
     */
    public function CheckKeyExist(string $sorKey = null, string $desKey = null, bool $withTrashed = false)
    {
        $map = [];
        if (isset($sorKey))
        {
            $map[] = [$this->_sorKey, '=', $sorKey];
        }
        if (isset($desKey))
        {
            $map[] = [$this->_desKey, '=', $desKey];
        }

        if ($withTrashed)
        {
            return $this->withTrashed()
                ->where($map)
                ->value($this->_sorKey, false);
        }
        else
        {
            return $this->where($map)
                ->value($this->_sorKey, false);
        }
    }

    /**
     * GetDesKeys
     *
     * @param string $userName
     *
     * @return array
     */
    public function GetDesKeys(string $sorKey = null)
    {
        if (is_null($sorKey))
        {
            return [];
        }

        return $this->distinct(true)
            ->where([$this->_sorKey => $sorKey])
            ->order('id')
            ->column($this->_desKey);
    }
}