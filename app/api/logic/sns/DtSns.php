<?php

namespace app\api\logic\sns;

use EasyDingTalk\Application;
use think\facade\Log;
use think\facade\Session;

/**
 * 第三方授权 - 钉钉
 */
class DtSns extends SnsAbstract
{
    private $_app      = null;
    private $_openType = null;
    private $_unionId  = null;
    private $_uInfo    = null;

    public function __construct()
    {
        $this->type = USEROAUTHS_TYPE_DINGTALK;

        $config          = yaconf('openlogin.dingtalk');
        $this->_app      = new Application($config);
        $this->_openType = $config['oauth']['app1']['openid'] ?? 'openid';
    }

    public function getRedirectUrl(string $authUrl, int $isLogin = YES)
    {
        //没有获取到粉丝的openid时，跳转到页面授权地址，进行登录/注册绑定
        $sessionId = Session::getid();

        $redirectUrl = $this->getDomain() . '/api/passport/wxlogin/baseinfo' . '?key=' .
            $sessionId . "&is_login=$isLogin" . '&type=' . $this->getType();

        $responseIns = $this->_app->oauth->use('app1')->redirect($redirectUrl, $authUrl);

        return $responseIns->getTargetUrl();
    }

    public function getRedirectInfoUrl(string $sessionId, string $authUrl)
    {
        return $this->getDomain() . '/api/passport/wxlogin/userinfo' . '?key=' .
            $sessionId . '&type=' . $this->getType() . '&state=' . $authUrl;
    }

    public function openId()
    {
        // 尝试获取 2 次
        $cnt = 2;
        do
        {
            //并向url参数中追加临时授权码code及state两个参数
            $result = $this->_app->oauth->use('app1')->user();
            if ($result)
            {
                if (isset($result['errcode']) && $result['errcode'] == 0)
                {
                    $this->_unionId = $result['user_info']['unionid'];
                    $this->_uInfo   = $result['user_info'];

                    return $result['user_info']['openid'];
                }
            }
        } while ((--$cnt) > 0);

        Log::error($result);
        return false;
    }

    /**
     * 根据unionid获取userid
     *
     * @return mixed
     */
    public function userId()
    {
        if(!$this->_unionId)
        {
            return false;
        }

        $userId     = false;
        $useridInfo = $this->_app->user->getUseridByUnionid($this->_unionId);
        if ($useridInfo)
        {
            if (isset($useridInfo['errcode']) && $useridInfo['errcode'] == 0)
            {
                $userId = dingtalk_name($useridInfo['userid']);
            }
        }

        return $userId;
    }

    public function isUserIdType()
    {
        return $this->_openType == 'userid';
    }

    public function getUserInfo()
    {
        if($this->_uInfo)
        {
            return $this->_uInfo;
        }

        //并向url参数中追加临时授权码code及state两个参数
        $result = $this->_app->oauth->use('app1')->user();
        if ($result)
        {
            if (isset($result['errcode']) && $result['errcode'] == 0)
            {
                /**
                 * 'nick' => '江加雄',
                 * 'unionid' => 'LOOCGCrrt6T9p4gYiikzE9AiEiE',
                 * 'dingId' => '$:LWCP_v1:$2KXahppb0keQr3jxQLOAZg==',
                 * 'openid' => '6j8E7aiSfdvmii7bOEpC0apAiEiE',
                 * 'main_org_auth_high_level' => true,
                 */
                $this->_unionId = $result['user_info']['unionid'];
                $this->_uInfo   = $result['user_info'];
                return $result['user_info'];
            }
        }

        return false;
    }
}