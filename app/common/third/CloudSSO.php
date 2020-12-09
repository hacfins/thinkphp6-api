<?php

namespace app\common\third;

use think\facade\Cookie;
use think\facade\Session;

/**
 * Single sign-on broker.
 * The broker lives on the website visited by the user. The broken doesn't have any user credentials stored. Instead it
 * will talk to the SSO server in name of the user, verifying credentials and getting user information.
 */
class CloudSSO
{
    public static $_error_code = \EC::SUCCESS;
    public static $_error_msg  = '';

    /**
     * Url of SSO server
     *
     * @var string
     */
    protected $_sso_url;

    /**
     * My identifier, given by SSO provider.
     *
     * @var string
     */
    public $_sso_broker;

    /**
     * My secret word, given by SSO provider.
     *
     * @var string
     */
    protected $_sso_secret;

    /**
     * Session token of the client
     *
     * @var string
     */
    public $_sso_token;

    /**
     * User info recieved from the server.
     *
     * @var array
     */
    protected $_userinfo;

    /**
     * Cookie lifetime
     *
     * @var int
     */
    protected $_cookie_lifetime;

    /**
     * access_key
     *
     * @var int
     */
    protected $_access_key;

    /**
     * access_secret
     *
     * @var int
     */
    protected $_access_secret;

    /**
     * Class constructor
     *
     * @param string $url    Url of SSO server
     * @param string $broker My identifier, given by SSO provider.
     * @param string $secret My secret word, given by SSO provider.
     */
    public function __construct($url, $broker, $secret, $access_key, $access_secret, $cookie_lifetime = 630720000)
    {
        if (!$url)
            throw new \InvalidArgumentException("SSO server URL not specified");
//        if (!$broker)
//            throw new \InvalidArgumentException("SSO broker id not specified");
//        if (!$secret)
//            throw new \InvalidArgumentException("SSO broker secret not specified");

        $this->_sso_url         = $url;
        $this->_sso_broker      = $broker;
        $this->_sso_secret      = $secret;
        $this->_access_key      = $access_key;
        $this->_access_secret   = $access_secret;
        $this->_cookie_lifetime = $cookie_lifetime;

        //if exist , set token value
        $cookeName        = $this->getCookieName();
        $this->_sso_token = Session::get($cookeName);
    }

    /**
     * Check if we have an SSO token.
     *
     * @return boolean
     */
    public function isAttached()
    {
        return $this->_sso_token;
    }

    /**
     * Attach our session to the user's session on the SSO server.
     *
     * @param string|true $returnUrl The URL the client should be returned to after attaching
     */
    public function attach($returnUrl = null)
    {
        if ($this->isAttached())
            return;

        $url = $this->getAttachUrl($returnUrl);

        header("Location: $url", true, 307);
        echo "You're redirected to <a href='$url'>$url</a>";
        exit();
    }

    public function getAttachUrl($returnUrl = null)
    {
        $params = [];

        if ($returnUrl === true)
        {
            $protocol  = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $returnUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            $params = ['return_url' => $returnUrl];
        }

        // Get URL to attach session at SSO server.
        // generate randon token of broker
        $this->generateToken();

        $data = [
            'broker'   => $this->_sso_broker,
            'token'    => $this->_sso_token,
            'checksum' => hash('sha256', 'attach' . $this->_sso_token . $this->_sso_secret)
        ];// + $_GET;

        return $this->_sso_url . '/api/passport/common/attach' . '?' . http_build_query($data + $params);
    }

    /**
     * Get user information.
     *
     * @return object|bool
     */
    public function getUserInfo($param = [], $bDirector = false)
    {
        if (!isset($this->_userinfo))
        {
            if ($bDirector)
                $this->_userinfo = $this->request_direct('GET', 'api/user/user/info', $param);
            else
                $this->_userinfo = $this->request('GET', 'api/user/user/info', $param);
        }

        return $this->_userinfo;
    }

    /**
     * 获取用户公开信息
     *
     * @param string $user_name
     * @param array  $param
     *
     * @return array|bool
     */
    public function getUserInfoEx(string $user_name, array $param = [], $bDirector = false)
    {
        if ($bDirector)
        {
            if(strlen($user_name) > 1000)
                return $this->request_direct('POST', 'api/user/user/info_ex', ['user_name' => $user_name] + $param);

            return $this->request_direct('GET', 'api/user/user/info_ex', ['user_name' => $user_name] + $param);
        }
        else
        {
            if(strlen($user_name) > 1000)
                return $this->request('POST', 'api/user/user/info_ex', ['user_name' => $user_name] + $param);

            return $this->request('GET', 'api/user/user/info_ex', ['user_name' => $user_name] + $param);
        }
    }

    /**
     * 退出登录
     *
     * @return array|bool
     */
    public function logOut($bDirector = false)
    {
        if ($bDirector)
            return $this->request_direct('GET', 'api/passport/user/logout', []);

        return $this->request('GET', 'api/passport/user/logout', []);
    }

    //==================================================== 内部接口 ======================================================

    /**
     * Get the cookie name.
     * Note: Using the broker name in the cookie name.
     * This resolves issues when multiple brokers are on the same domain.
     *
     * @return string
     */
    protected function getCookieName()
    {
        return 'sso_token_' . preg_replace('/[_\W]+/', '_', strtolower($this->_sso_broker));
    }

    /**
     * Generate session token
     */
    protected function generateToken()
    {
        if ($this->_sso_token)
            return;

        $this->_sso_token = base_convert(md5(uniqid(rand(), true)), 16, 36);
        Session::set($this->getCookieName(), $this->_sso_token);
        //setcookie($this->getCookieName(), $this->_sso_token, time() + $this->_cookie_lifetime, '/');
    }

    /**
     * Generate session id from session key
     *
     * @return string
     */
    protected function getSessionId()
    {
        if (!$this->_sso_token)
            return null;

        $checksum = hash('sha256', 'session' . $this->_sso_token . $this->_sso_secret);

        return "SSO-{$this->_sso_broker}-{$this->_sso_token}-$checksum";
    }

    /**
     * Clears session token
     */
    protected function clearToken()
    {
        Session::delete($this->getCookieName());
        $this->_sso_token = null;
    }

    /**
     * Execute on SSO server.
     *
     * @param string       $method HTTP method: 'GET', 'POST', 'DELETE'
     * @param string       $api    Command
     * @param array|string $param  Query or post parameters
     *
     * @return array|bool
     */
    private function request($method, $api, $param = array())
    {
        try
        {
            if (!$this->isAttached())
            {
                throw new \Exception('No token');
            }

            $curl         = new \Curl\Curl();
            $signatureCls = new \app\common\third\CloudSignature();

            //1.0 签名
            $signatureParms              = $signatureCls->params_signature($this->_access_key);
            $signatureParms['signature'] = $signatureCls->signature($this->_access_secret,
                array_merge($signatureParms, $param));

            //放入 Header 中
            //set broker 的 session id
            $signatureParms['Authorization'] = 'Hacfin ' . $this->getSessionID();
            $curl->setHeaders($signatureParms);

            // 其他参数放入请求中
            $url = $this->_sso_url . '/' . $api;

            //2.0 执行请求
            $curl->setTimeout(10);
            $curl->{$method}($url, $param);

            if ($curl->error)//失败
            {
                static::$_error_code = \EC::API_ERR;
                static::$_error_msg  = $curl->errorMessage;

                $curl->close();

                return false;
            }
            else
            {
                $response = $curl->response;
                if ($response->code == \EC::SSO_ATACHE_NOT_ERROR)
                {
                    $this->clearToken();
                }

                static::$_error_code = $response->code;
                static::$_error_msg  = $response->msg;

                $data = object2array($response->result ?? false);

                $curl->close();

                return $data;
            }
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();
        }

        $curl->close();

        return false;
    }

    private function request_direct($method, $api, $param = array())
    {
        try
        {
            $curl         = new \Curl\Curl();
            $signatureCls = new \app\common\third\CloudSignature();

            //1.0 签名
            $signatureParms              = $signatureCls->params_signature($this->_access_key);
            $signatureParms['signature'] = $signatureCls->signature($this->_access_secret,
                array_merge($signatureParms, $param));

            //放入 Header 中
            $curl->setHeaders($signatureParms);

            //set session id
            $curl->setCookies($_COOKIE);

            // 其他参数放入请求中
            $url = $this->_sso_url . '/' . $api;

            //2.0 执行请求
            $curl->setTimeout(10);
            $curl->{$method}($url, $param);

            if ($curl->error)//失败
            {
                static::$_error_code = \EC::API_ERR;
                static::$_error_msg  = $curl->errorMessage;

                $curl->close();

                return false;
            }
            else
            {
                //1.0 获取返回数据
                $response = $curl->response;
                if ($response->code == \EC::SSO_ATACHE_NOT_ERROR)
                {
                    $this->clearToken();
                }

                static::$_error_code = $response->code;
                static::$_error_msg  = $response->msg;

                $data = object2array($response->result ?? false);

                //2.0 设置cookie数据
                $responseCookies = $curl->responseCookies;
                $topDomain       = domain_top($this->_sso_url);
                foreach ($responseCookies as $cookie => $value)
                {
                    if ($value == 'deleted')
                    {
                        if ($topDomain)//跨域清除cookie
                        {
                            setcookie($cookie, '', $_SERVER['REQUEST_TIME'] - 3600, '/', $topDomain);
                        }
                        else
                        {
                            Cookie::delete($cookie, '');
                        }
                    }
                }

                $curl->close();

                return $data;
            }
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();
        }

        $curl->close();

        return false;
    }
}
