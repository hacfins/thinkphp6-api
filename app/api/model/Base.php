<?php

namespace app\api\model;

use app\api\model\log\LogDetails;
use think\facade\Cache;
use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;

/**
 * 模型层 - 基类
 *
 * 模型层的单条信息有查询缓存，并且在更新、删除操作会同步缓存信息
 *
 */
class Base extends Model
{
    protected $_lk        = 'id';
    protected $dateFormat = 'Y-m-d H:i:s';

    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    // +--------------------------------------------------------------------------
    // |  override
    // +--------------------------------------------------------------------------
    /**
     * 根据逻辑Id号，获取记录信息列表（使用cache）
     *
     * @param mixed $lkId        逻辑Id号
     * @param bool  $withTrashed 是否包含函数数据
     *
     * @return bool
     */
    public function GetInfo($lkId, bool $withTrashed = false)
    {
        return false;
    }

    // +--------------------------------------------------------------------------
    // |  缓存 - 模型层
    // +--------------------------------------------------------------------------
    /**
     * 判断缓存是否存在
     *
     * @access public
     *
     * @param string $name 缓存变量名
     *
     * @return bool
     */
    protected function Cache_Has($name)
    {
        $name = $this->Cache_Key($name);

        return Cache::has($name);
    }

    /**
     * 读取缓存
     *
     * @access public
     *
     * @param string $name    缓存标识
     * @param mixed  $default 默认值
     *
     * @return mixed
     */
    protected function Cache_Get($name, $default = false)
    {
        $name = $this->Cache_Key($name);

        return Cache::get($name, $default);
    }

    /**
     * 写入缓存
     *
     * @access public
     *
     * @param string   $name   缓存标识
     * @param mixed    $value  存储数据
     * @param int|null $expire 有效时间 0为永久
     *
     * @return boolean
     */
    protected function Cache_Set($name, $value, $expire = CACHE_TIME_SQL)
    {
        $name = $this->Cache_Key($name);

        return Cache::set($name, $value, $expire);
    }

    /**
     * 删除缓存
     *
     * @access public
     *
     * @param string $name 缓存标识
     *
     * @return boolean
     */
    protected function Cache_Rm($name)
    {
        $name = $this->Cache_Key($name);

        return Cache::delete($name);
    }

    /**
     * 缓存标签
     *
     * @access public
     *
     * @param string                 $name   标签名
     * @param string|array           $values 缓存数据
     * @param null|int|\DateInterval $ttl    有效时间 0为永久
     */
    protected function Cache_Tag($name, $values, $ttl = null)
    {
        return Cache::tag($name)->setMultiple($values, $ttl);
    }

    /**
     * 清除缓存
     *
     * @access public
     *
     * @param string $tag 标签名
     *
     * @return boolean
     */
    public function Cache_clear($tag = null)
    {
        return Cache::tag($tag)->clear();
    }

    /**
     * 模型层单条记录缓存的key
     *
     * @param string $key 模型的id号、guid等，表示唯一值
     *
     * @return string
     */
    protected function Cache_Key(string $key)
    {
        // 查询缓存，可用于 select、find、value和column方法，以及其衍生方法
        //
        // 当你删除或者更新数据的时候，可以使用cache方法手动更新（清除）缓存
        // Db::table('think_user')->cache('user_data')->select([1,3,5]);
        // Db::table('think_user')->cache('user_data')->update(['id'=>1,'name'=>'thinkphp']);
        // Db::table('think_user')->cache('user_data')->select([1,5]);

        // 数据库查询缓存的Key，由 表名 + 唯一值（如id等）构成
        return $this->name . strtolower($key);
    }

    // +--------------------------------------------------------------------------
    // |  数据操作
    // +--------------------------------------------------------------------------
    /**
     * 更新数据 - 较为适合单条数据的更新 & 不含删除记录
     *
     * @param string $cacheKey 缓存的Key
     * @param null   $where    更新条件
     * @param array  $data     更新数据
     *
     * @return int|string
     */
    protected function Db_Update(string $cacheKey, $where = null, array $data = [])
    {
        Db::startTrans();
        try
        {
            $rtn = true;
            if ($this->_bChanged)
            {
                $model = new static();
                $model->withTrashed = true;
                //查询时，使用缓存
                if ((count($where) == 1) && isset($where[$this->_lk]))
                {
                    $model = $model->cache($this->Cache_Key($cacheKey), CACHE_TIME_SQL)
                        ->where($where)
                        ->find();
                    if($model)
                    {
                        $rtn = $model->save($data);
                    }
                }
                else
                {
                    $model = $model->where($where)
                        ->select();
                    if($model)
                    {
                        $rtn = $model->update($data);
                    }
                }

                //缓存同步
                $this->Cache_Rm($cacheKey);
                $this->Cache_Rm($cacheKey . CACHE_WITHTRASHED);
            }

            Db::commit();
            return $rtn;
        }
        catch (\Throwable $e)
        {
            Db::rollback();
            E($e->getCode(), $e->getMessage());
        }
    }

    // +--------------------------------------------------------------------------
    // |  日志操作 - 数据库事件监听
    // +--------------------------------------------------------------------------
    protected $_isNeedLog = true;  // 是否需要插入日志
    protected $_bChanged  = true;  // 数据是否发生改变

    public static function onAfterInsert(Model $model)
    {
        //return self::AddLogDetails($model, LOGOP_OP_TYPE_ADD);
    }

    public static function onbeforeUpdate(Model $model)
    {
        return self::AddLogDetails($model, LOGOP_OP_TYPE_MODIFY);
    }

    public static function onafterDelete(Model $model)
    {
        //return self::AddLogDetails($model, LOGOP_OP_TYPE_REMOVE);
    }

    /**
     * 添加日志记录
     *
     * @param     $model
     * @param int $type
     *
     * @return bool
     */
    private static function AddLogDetails($model, $type = LOGOP_OP_TYPE_ADD)
    {
        if ($model->_isNeedLog)
        {
            $tableName   = $model->getTable();
            $doSomething = '';

            //忽略系统内部表的相关记录
            if (strpos($tableName, 'log') === false && strpos($tableName, 'conf') === false)
            {
                switch ($type)
                {
                    case LOGOP_OP_TYPE_ADD:
                        $doSomething = '新增了 ' . $tableName . ' 记录';
                        break;
                    case LOGOP_OP_TYPE_MODIFY:
                        {
                            //获取变更内容
                            //可能没有任何内容的变更，但也需要返回 true
                            $doSomething      = self::GetDoSomething($model);
                            $model->_bChanged = $doSomething ? true : false;
                        }
                        break;
                    case LOGOP_OP_TYPE_REMOVE:
                        $doSomething = '删除了 ' . $tableName . ' 记录';
                        break;
                }

                global $g_logs_optype;

                if ($doSomething && ($g_logs_optype == $type) && in_array($type, LOGOP_OP_TYPE_ARR))
                {
                    //获取有效的逻辑值
                    $lkName  = $model->_lk;
                    $lkValue = $model->{$lkName} ?? false;
                    if (!$lkValue)
                    {
                        $where = $model->getWhere();
                        if ($where && isset($where[$lkName]))
                        {
                            $lkValue = $where[$lkName];
                        }
                    }

                    if ($lkValue)
                    {
                        LogDetails::Add($tableName, $lkValue, $doSomething);
                    }
                }
            }
        }

        return true;
    }

    /**
     * 获取修改变化内容.
     *
     * @param $model
     *
     * @return string | bool
     */
    private static function GetDoSomething($model)
    {
        try
        {
            //如果主键和更新条件都为空
            if (!$model->getKey() && !$model->getWhere())
            {
                return false;
            }

            //获取修改的数据
            $editData = $model->getChangedData();

            //移除更新时间字段
            if (isset($editData['update_time']))
            {
                unset($editData['update_time']);
            }

            //查询字段注释
            $columns    = Db::query("show full columns from {$model->getTable()}");
            $columnsArr = [];

            //组合为 key 为 field value 为 Comment 的数组
            foreach ($columns as $field)
            {
                $fieldName = $field['Field'];

                //只处理更新的字段注释
                if (array_key_exists($fieldName, $editData))
                {
                    $comment = $field['Comment'];

                    //先替换中文（为英文(
                    $comment = str_replace('（', '(', $comment);

                    //截取括号前面的字段注释
                    $pos = intval(strpos($comment, '('));
                    if ($pos)
                    {
                        $comment = substr($comment, 0, $pos);
                    }

                    $columnsArr[$fieldName] = !$comment ? $fieldName : $comment;
                }
            }

            $doSomething = '修改了 ' . $model->getTable() . ' 记录，';

            //修改内容
            $editContent = $doSomething;

            //需要查询验证的字段字符串
            if ($model->getKey())
            {
                $tableWhere = [
                    $model->getPk() => $model->getKey(),
                ];
            }
            else
            {
                $tableWhere = $model->getWhere();
            }

            //从缓存中获取，获取不到时，再实时获取
            $lkName = $model->_lk;
            if (isset($tableWhere[$lkName]) && ($oldData = $model->GetInfo($tableWhere[$lkName])))
            {
                $oldData[$lkName] = $tableWhere[$lkName];
            }
            else
            {
                $fieldStr = "$lkName,";
                foreach ($editData as $editKey => $editVal)
                {
                    $fieldStr .= $editKey . ',';
                }
                $fieldStr = rtrim($fieldStr, ',');

                $oldData = $model->field($fieldStr)
                    ->where($tableWhere)
                    ->find();
            }
            if (!$oldData)
            {
                return false;
            }
            $model->{$lkName} = $oldData[$lkName];

            /*
            * 循环日志判断字段数组变化
            * */
            foreach ($editData as $key => $value)
            {
                $oldValue = $oldData[$key];
                $newValue = $editData[$key];

                if ($oldValue != $newValue)
                {
                    if (null == $oldValue || '' == $oldValue)
                    {
                        $editContent .= '"' . $columnsArr[$key] . '"添加了值 ' . $newValue . '；';
                    }
                    else
                    {
                        $editContent .= '"' . $columnsArr[$key] . '"从 ' . $oldValue . ' 改为了 ' . $newValue . '；';
                    }
                }
            }

            //如果没有数据改变，则不插入日志记录
            if ($editContent == $doSomething)
            {
                return false;
            }

            return $editContent;
        }
        catch (\Throwable $exception)
        {
            return false;
        }
    }
}