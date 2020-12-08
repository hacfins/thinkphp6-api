<?php

namespace app\api\logic\sns;

use think\facade\Session;

/**
 * 第三方授权 - 微信
 */
class WxSns extends SnsAbstract
{
    private $_oauth = null;

    public function __construct()
    {
        $this->type   = USEROAUTHS_TYPE_WEIXIN;
    }

    private static function GetWxOptions()
    {
        //公众号配置文件
        $options = yaconf('openlogin.weixin');

        $options['mch_key']    = $options['partnerkey'];
        $options['cache_path'] = runtime_path() . 'log/' . 'data/';

        return $options;
    }

    public static function JsSDKSign(string $url)
    {
        try
        {
            // 创建SDK实例
            $script = new \WeChat\Script(self::GetWxOptions());

            // 获取JsApi使用签名，通常这里只需要传 $url参数
            $result = $script->getJsSign($url);

            return $result;
        }
        catch (\Throwable $e)
        {
            //E(\EC::DB_OPERATION_ERROR, $e->getMessage());
        }

        return false;
    }

    public function getRedirectUrl(string $authUrl, int $isLogin = YES)
    {
        if(!$this->_oauth)
        {
            $this->_oauth = new \WeChat\Oauth(self::GetWxOptions());
        }

        //没有获取到粉丝的openid时，跳转到页面授权地址，进行登录/注册绑定
        $sessionId = Session::getid();

        $redirectUrl = $this->getDomain() . '/api/passport/wxlogin/baseinfo' . '?key=' .
            $sessionId . "&is_login=$isLogin" . "&type=" . $this->getType();

        return $this->_oauth->getOauthRedirect($redirectUrl, $authUrl, 'snsapi_base');
    }

    public function getRedirectInfoUrl(string $sessionId, string $authUrl)
    {
        if(!$this->_oauth)
        {
            $this->_oauth = new \WeChat\Oauth(self::GetWxOptions());
        }

        return $this->_oauth->getOauthRedirect($this->getDomain() . '/api/passport/wxlogin/userinfo' .
            '?key=' . $sessionId . "&type=" . $this->getType(), $authUrl, 'snsapi_userinfo');
    }

    public function openId()
    {
        if(!$this->_oauth)
        {
            $this->_oauth = new \WeChat\Oauth(self::GetWxOptions());
        }

        // 尝试获取 2 次
        $cnt = 2;
        do
        {
            // 通过 code 获取 AccessToken 和 openid
            $result = $this->_oauth->getOauthAccessToken();
            if ($result)
            {
                break;
            }
        } while ((--$cnt) > 0);

        if ($result)
        {
            return $result['openid'];
        }

        return $result;
    }

    /**
     * headimgurl
     * nickname
     *
     * @return array|false
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function getUserInfo()
    {
        if(!$this->_oauth)
        {
            $this->_oauth = new \WeChat\Oauth(self::GetWxOptions());
        }

        $result = $this->_oauth->getOauthAccessToken();

        //获取的 AccessToken 成功
        if ($result)
        {
            // 获取授权后的用户资料
            return $this->_oauth->getUserInfo($result['access_token'], $result['openid']);
        }

        return false;
    }
}