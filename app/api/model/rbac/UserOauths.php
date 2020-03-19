<?php
namespace app\api\model\rbac;

use app\api\model\Base;
use app\common\traits\Instance;

/*
 * 用户第三方授权信息表
 *
 * 1、缓存:
 *     key: oauth_id
 */
class UserOauths extends Base
{
    protected $_lk = 'oauth_id';

    use Instance;

    //只读字段
    protected $readonly = ['oauth_id'];

    //自动时间
    protected $autoWriteTimestamp = 'datetime';

    // +--------------------------------------------------------------------------
    // |  基本操作
    // +--------------------------------------------------------------------------
    /**
     * 绑定新用户
     *
     * @param string $oauth_id
     * @param string $user_name
     * @param int    $oauth_type
     *
     * @return
     */
    public function Add(string $oauth_id, string $user_name, $oauth_type = USEROAUTHS_TYPE_WEIXIN)
    {
        return self::create([
                'user_name'  => $user_name,
                'oauth_id'   => $oauth_id,
                'oauth_type' => $oauth_type
            ]);
    }

    /**
     * 删除绑定的用户
     *
     * @param string $oauth_id
     */
    public function Del(string $oauth_id)
    {
        //同步缓存
        $this->Cache_Rm($oauth_id);
        $this->Cache_Rm($oauth_id . CACHE_WITHTRASHED);

        $this->destroy(['oauth_id' => $oauth_id]);
    }

    public function GetInfo($oauth_id, bool $withTrashed = false)
    {
        $field = [
            'oauth_id',
            'user_name',
            'oauth_type',
            'update_time',
            'create_time'
        ];

        if ($withTrashed)
        {
            $user = self::withTrashed()->field($field)
                ->cache($this->Cache_Key($oauth_id . CACHE_WITHTRASHED), CACHE_TIME_SQL)
                ->where(['oauth_id' => $oauth_id])
                ->find();
        }
        else
        {
            $user = $this->field($field)
                ->cache($this->Cache_Key($oauth_id), CACHE_TIME_SQL)
                ->where(['oauth_id' => $oauth_id])
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
     * 检测用户是否绑定
     *
     * @param string $oauth_id
     *
     * @return mixed 用户名
     */
    public function CheckExist(string $oauth_id)
    {
        $info = $this->GetInfo($oauth_id);
        return $info['user_name'] ?? false;
    }

    /**
     * 检测用户是否绑定
     *
     * @param string $user_name
     *
     * @return mixed oauth_id
     */
    public function CheckExistByName(string $user_name, $oauth_type = USEROAUTHS_TYPE_WEIXIN)
    {
        return $this->where([
            ['user_name', '=', $user_name],
            ['oauth_type', '=', $oauth_type]
        ])
            ->value('oauth_id', false);
    }
}