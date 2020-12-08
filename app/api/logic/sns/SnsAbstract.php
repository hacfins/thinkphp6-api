<?php

namespace app\api\logic\sns;

use think\facade\Cache;
use think\facade\Request;
use think\facade\Session;

/**
 * 所有第三方登录必须支持的接口方法
 */
abstract class SnsAbstract implements SnsInterface
{
    protected $type = USEROAUTHS_TYPE_WEIXIN;

    public const CACHE_TIME = 1800;
    // 半小时

    // 状态
    public const WX_LOGIN_STATUE_QRCODE            = 1; // - 获取到二维码
    public const WX_LOGIN_STATUE_ACCESSTOKEN_FAILD = 2; // - 获取的AccessToken失败
    public const WX_LOGIN_STATUE_USER_REMOVE       = 3; // - 绑定的用户被删除
    public const WX_LOGIN_STATUE_LOGIN_SUCESS      = 4; // - 登录成功
    public const WX_LOGIN_STATUE_OAUTH_SUCCESS     = 5; // - 成功获取用户授权信息，等待登录/注册绑定
    public const WX_LOGIN_STATUE_USER_EXIST        = 6; // - 此账号已与平台其他账号绑定
    public const WX_LOGIN_STATUE_BLIND_SUCESS      = 7; // - 绑定成功

    public function getType()
    {
        return $this->type;
    }

    public function getName()
    {
        $name = '';
        switch ($this->type)
        {
            case USEROAUTHS_TYPE_WEIXIN:
                $name = '第三方账号微信';
                break;
            case USEROAUTHS_TYPE_DINGTALK:
                $name = '第三方账号钉钉';
                break;
            default:
                $name = '错误';
        }

        return $name;
    }

    public function getDomain()
    {
        //return 'http://hacfin.vaiwan.com';
        return Request::domain();
    }

    public function getValidUserName(string $openId)
    {
        //钉钉的用户Id号可能有 - ,此时需要特殊处理
        $openId = str_replace('-', '', $openId);
        if (strlen($openId) > 19)
        {
            $openId = substr($openId, 0, 19);
        }

        return 'z' . $openId;
    }

    // +--------------------------------------------------------------------------
    // |  Cache
    // +--------------------------------------------------------------------------
    private function CacheKey($key)
    {
        if (is_null($key))
            $key = $this->type . CACHE_OAUTH_OPENID . Session::getid();
        else
            $key = $this->type . CACHE_OAUTH_OPENID . $key;

        return $key;
    }

    /**
     * @param null        $key          缓存的key
     * @param null        $create_time  缓存创建时间
     * @param null        $statue       第三方登录的状态
     *
     * @param null        $msg
     * @param null        $access_token 登录成功返回的Token
     * @param null        $openid
     * @param string|null $headimgurl
     * @param string|null $userName
     * @param int|null    $isLogin
     * @param string|null $fullName
     * @param int|null    $num
     */
    public function CacheQrCode(
        $key = null, $create_time = null, $statue = null, $msg = null, $access_token = null,
        $openid = null, string $headimgurl = null, string $userName = null,
        int $isLogin = null, string $fullName = null, int $num=null)
    {
        $key    = $this->CacheKey($key);
        $qrcode = Cache::get($key);
        if (!$qrcode)
        {
            $qrcode = [];
        }

        // 创建时间
        if (isset($create_time))
        {
            $qrcode['create_time'] = $create_time;
        }
        // 状态
        if (isset($statue))
        {
            $qrcode['statue'] = $statue;
        }
        if(isset($msg))
        {
            $qrcode['msg'] = $msg;
        }
        // 用户名
        if (isset($userName))
        {
            $qrcode['user_name'] = $userName;
        }
        if (isset($fullName))
        {
            $qrcode['full_name'] = $fullName;
        }
        // 登录后的凭证
        if (isset($access_token))
        {
            $qrcode['sg'] = $access_token;
        }
        // 第三方唯一标识符
        if (isset($openid))
        {
            $qrcode['openid'] = $openid;
        }
        // 第三方用户头像
        if (isset($headimgurl))
        {
            $qrcode['headimgurl'] = $headimgurl;
        }
        // 是否是登录请求
        if (isset($isLogin))
        {
            $qrcode['is_login'] = $isLogin;
        }

        //为了解决Android手机调用两次的问题
        if(isset($num))
        {
            $qrcode['invokes'] = $num;
        }

        Cache::set($key, $qrcode, self::CACHE_TIME);
    }

    /**
     * 删除缓存
     *
     * @param null $key
     */
    public function CacheRmQrCode($key = null)
    {
        $key = $this->CacheKey($key);
        Cache::delete($key);
    }

    public function CacheGetQrCode($key = null)
    {
        $key = $this->CacheKey($key);

        return Cache::get($key);
    }
}