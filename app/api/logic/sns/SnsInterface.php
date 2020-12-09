<?php

namespace app\api\logic\sns;

/**
 * 所有第三方登录必须支持的接口方法
 */
interface SnsInterface
{
    /**
     * 获取openid的跳转地址
     *
     * @param string $authUrl
     * @param int    $isLogin
     */
    public function getRedirectUrl(string $authUrl, int $isLogin = YES);

    /**
     * 获取用户信息的跳转地址
     *
     * @param string $sessionId
     * @param string $authUrl
     *
     * @return mixed
     */
    public function getRedirectInfoUrl(string $sessionId, string $authUrl);

    /**
     * 获取当前授权用户的openid标识
     */
    public function openId();

    /**
     * 获取格式化后的用户信息
     *
     * openid
     * headimgurl
     * ...
     * 
     */
    public function getUserInfo();
}
