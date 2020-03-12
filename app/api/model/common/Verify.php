<?php
namespace app\api\model\common;

use app\common\traits\Instance;
use think\facade\{
    Session
};

/*
 * 验证码
 */
class Verify
{
    use Instance;

    /**
     * 输出验证码
     *
     * 验证码的值保存的session中验证码保存到session的格式为：
     *  array('verify_code' => '验证码值', 'verify_time' => '验证码创建时间');
     *
     * @param string $key  手机号/邮箱
     * @param string $type 类型
     *
     * @return string
     */
    public function Verify_Generate(string $key, $type=SESSIONID_VERIFY_REGISTER)
    {
        $key = $this->GetKey($key, $type);

        // 保存验证码
        $code                  = \PhpCrypt::Random_Pwd(4, true);
        $secode                = [];
        $secode['verify_code'] = $code;  // 把校验码保存到session
        $secode['verify_time'] = time(); // 验证码创建时间

        Session::set($key, $secode);

        return $code;
    }

    /**
     * 验证验证码是否正确
     *
     * @param string $code 用户验证码
     * @param string $type 验证码类型
     *
     * @return bool 用户验证码是否正确
     */
    public function Verify_Check(string $key, $type=SESSIONID_VERIFY_REGISTER,  string $code='', bool $delSession = false)
    {
        $key = $this->GetKey($key, $type);

        // 验证码不能为空
        $secode = Session::get($key);
        if (empty($code) || empty($secode))
        {
            E(\EC::VERIFYCODE_ERROR, null, false);
        }

        // session 过期 （30分钟）
        if (time() - $secode['verify_time'] > 1800)
        {
            Session::delete($key);
            E(\EC::VERIFYCODE_EXPIRE, null, false);
        }

        if (strtolower($code) == strtolower($secode['verify_code']))
        {
            if ($delSession)
                Session::delete($key);

            return true;
        }

        return false;
    }

    /**
     * 删除验证码
     */
    public function Verify_Del(string $key, $type=SESSIONID_VERIFY_REGISTER)
    {
        $key = $this->GetKey($key, $type);

        Session::delete($key);
    }

    private function GetKey(string $key, $type=SESSIONID_VERIFY_REGISTER)
    {
        return 'verify_' . $type . md5($key);
    }
}