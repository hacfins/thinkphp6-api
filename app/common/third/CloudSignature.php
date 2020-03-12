<?php

namespace app\common\third;

/**
 * 内部程序应用接入授权抽象类
 */
class CloudSignature
{
    /**
     * 请求签名
     *
     * @param string $secret_key
     * @param string $params 包含Header中的请求参数列表
     *
     * @return string signature
     */
    public function signature(string $secret_key, array $params)
    {
        // sort
        ksort($params);

        // merge param
        $sortedQueryStringTmp = '';
        foreach ($params as $key => $value)
        {
            $sortedQueryStringTmp .= '&' . $this->safe_encode($key) . '=' . $this->safe_encode($value);
        }

        // sign
        $stringToSign = "GET&%2F&" . $this->safe_encode(substr($sortedQueryStringTmp, 1));
        $sign         = base64_encode(hash_hmac('sha1', $stringToSign, $secret_key . '&', true));
        $signature    = $this->safe_encode($sign);

        return $signature;
    }

    /**
     * 生成签名参数列表
     *
     * @param string $access_key
     * @param array  $params
     *
     * @return array
     */
    public function params_signature(string $access_key)
    {
        return [
            'signaturemethod'  => 'hmac-sha1',
            'signaturenonce'   => uniqid(mt_rand(0, 0xffff), true), //防止截获攻击
            'signatureversion' => '1.0',
            'accesskey'        => $access_key,
            'timestamp'        => gmdate("Y-m-d\TH:i:s\Z"), //防止截获攻击
            'format'           => 'json',
        ];
    }

    /**
     * 参数编码
     */
    public function safe_encode($str)
    {
        $res = urlencode($str);

        $res = preg_replace("/\+/", "%20", $res); //+
        $res = preg_replace("/\*/", "%2A", $res); //*
        $res = preg_replace("/%7E/", "~", $res);  //~

        return $res;
    }
}
