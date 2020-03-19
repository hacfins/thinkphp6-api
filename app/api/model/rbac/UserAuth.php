<?php

namespace app\api\model\rbac;

use app\api\model\Base;
use app\common\traits\Instance;

/*
 * 用户本地授权信息表
 *
 * 1、缓存:
 *     key: user_name
 */
class UserAuth extends Base
{
    protected $_lk = 'user_name';

    use Instance;

    //只读字段
    protected $readonly = ['user_name'];

    //自动时间
    protected $autoWriteTimestamp = 'datetime';

    // +--------------------------------------------------------------------------
    // |  基本操作
    // +--------------------------------------------------------------------------
    /**
     * 添加本地授权
     *
     * @param string $user_name 用户名
     * @param string $pwd       密码
     * @param string $phone     电话号码
     * @param string $email     邮箱
     *
     */
    public function Add(string $user_name, string $pwd='', string $phone='', string $email='')
    {
        self::create([
                'user_name' => $user_name,
                'pwd'       => $this->Crypt_Pwd($pwd),
                'phone'     => $phone,
                'email'     => $email,
            ]);
    }

    /**
     * 修改本地授权
     *
     * @param string      $user_name
     * @param string|null $pwd
     * @param string|null $phone
     * @param string|null $email
     *
     * @return int|string
     */
    public function Modify(string $user_name, string $pwd=null, string $phone=null, string $email=null)
    {
        $data = [];
        if (isset($pwd))
        {
            $data['pwd'] = $pwd;
        }
        if (isset($phone))
        {
            $data['phone'] = $phone;
        }
        if (isset($email))
        {
            $data['email'] = $email;
        }

        //同步缓存
        $this->Cache_Rm($user_name . CACHE_WITHTRASHED);
        $this->Cache_Rm($user_name);

        return $this->Db_Update($user_name, ['user_name' => $user_name], $data);
    }

    /**
     * 修改密码
     *
     * @param string $user_name 用户名
     * @param string $pwd       新密码
     *
     * @return false|int
     */
    public function ModifyPwd(string $user_name, string $pwd)
    {
        return $this->Modify($user_name, $this->Crypt_Pwd($pwd));
    }

    /**
     * 修改手机
     *
     * @param string $user_name 用户名
     * @param string $phone     新手机
     *
     * @return false|int
     */
    public function ModifyPhone(string $user_name, string $phone)
    {
        return $this->Modify($user_name, null, $phone);
    }

    /**
     * 修改邮箱
     *
     * @param string $user_name 用户名
     * @param string $email     新邮箱
     *
     * @return false|int
     */
    public function ModifyEmail(string $user_name, string $email)
    {
        return $this->Modify($user_name, null, null, $email);
    }

    /**
     * 软删除
     *
     * @param array $user_names 用户名
     *
     * @return int
     */
    public function Dels(array $user_names)
    {
        //同步缓存
        foreach ($user_names as $user_name)
        {
            $this->Cache_Rm($user_name);
            $this->Cache_Rm($user_name . CACHE_WITHTRASHED);
        }

        return self::destroy(function ($query) use ($user_names){
            $query->where('user_name', 'IN', $user_names);
        });
    }

    /**
     * 密码加密
     *
     * @param string $pwd 密码
     *
     * @return string
     */
    protected function Crypt_Pwd(string $pwd)
    {
        //密码加密传输
        if(yaconf('encrypt.pwd'))
            $pwd = md5($pwd);

        //Creates a password hash.
        return password_hash($pwd, PASSWORD_DEFAULT);
    }

    /**
     * 获取用户授权信息
     *
     * @param string $user_name  用户名
     * @param bool   $withTrashed 是否包含删除数据
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetInfo($user_name, bool $withTrashed=false)
    {
        $field = [
            'pwd',
            'phone',
            'email'
        ];

        if ($withTrashed)
        {
            $user = self::withTrashed()->field($field)
                ->cache($this->Cache_Key($user_name . CACHE_WITHTRASHED), CACHE_TIME_SQL)
                ->where(['user_name' => $user_name])
                ->find();
        }
        else
        {
            $user = $this->field($field)
                ->cache($this->Cache_Key($user_name), CACHE_TIME_SQL)
                ->where(['user_name' => $user_name])
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
     * 检查邮箱是否存在
     *
     * @param string $email      要检查的内容
     * @param string $exceptName 要排除的用户名
     *
     * @return bool|mixed false or 用户名
     */
    public function CheckExist_Email(string $email, string $exceptName = null)
    {
        $map = [];
        if (isset($exceptName))
        {
            $map[] = [
                'user_name',
                '<>',
                $exceptName,
            ];
        }

        $map[] = ['email', '=', $email];

        return $this->withTrashed()
            ->where($map)
            ->value('user_name', false);
    }

    /**
     * 检查手机号是否存在
     *
     * @param string $phone      要检查的内容
     * @param string $exceptName 要排除的用户名
     *
     * @return bool|mixed false or 用户名
     */
    public function CheckExist_Phone(string $phone, string $exceptName = null)
    {
        $map = [];
        if (isset($exceptName))
        {
            $map[] = [
                'user_name',
                '<>',
                $exceptName,
            ];
        }

        $map[] = ['phone', '=', $phone];

        return $this->withTrashed()
                ->where($map)
                ->value('user_name', false);
    }
}