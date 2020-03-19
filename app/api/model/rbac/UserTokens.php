<?php

namespace app\api\model\rbac;

use app\api\model\Base;
use app\common\traits\Instance;

use app\common\facade\Os;

/*
 * 用户登录信息表
 *
 * 1、缓存:
    key: token_id
 */

class UserTokens extends Base
{
    protected $_lk = 'token_id';

    use Instance;

    //只读字段
    protected $readonly = ['token_id'];

    //自动时间
    protected $autoWriteTimestamp = 'datetime';

    protected function setOsTypeAttr()
    {
        return osname_to_num(Os::getName()); // windows/os x / linux / ios / android;
    }

    /**
     * 创建令牌
     *
     * @param string $user_name 用户名
     *
     * @return string | false 令牌
     */
    public function Add(string $user_name, int $bFreelogin = YES)
    {
        $token_id   = guid();
        $expireTime = ($bFreelogin == YES) ? (datatime_add_seconds(USERTOKENS_TOKEN_EXPIRES_LONG)) :
            (datatime_add_seconds(USERTOKENS_TOKEN_EXPIRES));

        $rtn = self::create([
            'token_id'  => $token_id,
            'user_name' => $user_name,
            'expire'    => $expireTime,

            'os_type' => 0,
            'status'  => USERTOKENS_STATUE_ENABLED,
        ]);

        return $rtn ? $token_id : false;
    }

    /**
     * 根据令牌获取用户名
     *
     * @param string $token_id
     *
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetInfo($token_id, bool $withTrashed = false)
    {
        $info = $this->field(['user_name', 'status', 'expire', 'os_type'])
            ->cache($this->Cache_Key($token_id), CACHE_TIME_SQL)
            ->where('token_id', '=', $token_id)
            ->find();

        if (!$info)
            return [];

        return $info->toArray();
    }

    /**
     * 获取某用户的其他令牌
     *
     * @param string $user_name       用户名
     * @param string $os_type         平台类型（windows/os x / linux / ios / android）
     * @param string $except_token_id 排除的令牌
     *
     * @return array|null
     */
    public function GetOtherTokens(string $user_name, int $os_type = null, string $except_token_id = null)
    {
        if (!isset($user_name))
            return [];

        $map   = [];
        $map[] = ['user_name', '=', $user_name];
        $map[] = ['status', '=', USERTOKENS_STATUE_ENABLED];
        if (isset($os_type))
        {
            $map[] = ['os_type', '=', $os_type];
        }
        if (isset($except_token_id))
        {
            $map[] = ['token_id', '<>', $except_token_id];
        }

        return $this->where($map)
            ->column('token_id');
    }

    /**
     * 掉线
     *
     * @param string $token
     */
    public function OffLine(array $token_ids)
    {
        //同步缓存
        foreach ($token_ids as $token_id)
        {
            $this->Cache_Rm($token_id);
        }

        $map[] = ['token_id', 'IN', $token_ids];
        $data  = [
            'status' => USERTOKENS_STATUE_OFFLINE,
        ];

        return $this->Db_Update('token_ids', $map, $data);
    }

    /**
     * 软删除-根据令牌
     *
     * @param array $token_ids
     *
     * @return int 成功删除的记录数
     */
    public function DelByTokens(array $token_ids)
    {
        //同步缓存
        foreach ($token_ids as $token_id)
        {
            $this->Cache_Rm($token_id);
        }

        return self::destroy(function ($query) use ($token_ids) {
            $query->where('token_id', 'IN', $token_ids);
        });
    }

    /**
     * 软删除-根据用户
     *
     * @param string $userName 用户名
     *
     * @return int
     */
    public function DelByUsers(array $user_names)
    {
        //同步缓存信息
        foreach ($user_names as $userName)
        {
            $token_ids = $this->GetOtherTokens($userName);
            $this->DelByTokens($token_ids);
        }
    }
}