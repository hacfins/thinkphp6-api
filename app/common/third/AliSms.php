<?php
namespace app\common\third;

/**
 * 阿里云短信验证码发送类
 */
class AliSms
{
    public $error; // 保存错误信息

    private $domain          = 'dysmsapi.aliyuncs.com';
    private $accessKeyId     = ''; // Access Key ID
    private $accessKeySecret = ''; // Access Access Key Secret
    private $signName        = ''; // 签名
    private $templateCode    = ''; // 模版ID
    private $templateParam   = '';   // 设置模板参数, 假如模板中存在变量需要替换则为必填项

    public function __construct($config = array())
    {
        $this->accessKeyId     = $config['accessKeyId'];
        $this->accessKeySecret = $config['accessKeySecret'];
        $this->signName        = $config['signName'];
        $this->templateCode    = $config['templateCode'];
        $this->templateParam   = $config['templateParam'];
    }

    /**
     * 生成签名并发起请求
     *
     * @param $security        boolean 使用https
     *
     * @return bool|\stdClass 返回API接口调用结果，当发生错误时返回false
     */
    public function request($phone, $security = false)
    {
        $apiParams = array_merge(array(
            'SignatureMethod'  => 'HMAC-SHA1',
            'SignatureNonce'   => uniqid(mt_rand(0, 0xffff), true),
            'SignatureVersion' => '1.0',
            'AccessKeyId'      => $this->accessKeyId,
            'Timestamp'        => gmdate("Y-m-d\TH:i:s\Z"),
            'Format'           => 'JSON',

            'RegionId' => 'cn-hangzhou',
            'Action'   => 'SendSms',
            'Version'  => '2017-05-25',
        ), array(
            'PhoneNumbers'  => $phone,
            'SignName'      => $this->signName,
            'TemplateCode'  => $this->templateCode,
            'TemplateParam' => json_encode($this->templateParam, JSON_UNESCAPED_UNICODE),
        ));

        //设置发送短信流水号
        //$apiParams['OutId'] = '12345';

        //上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        //$apiParams['SmsUpExtendCode'] = '1234567';

        // 签名
        ksort($apiParams);

        $sortedQueryStringTmp = '';
        foreach ($apiParams as $key => $value)
        {
            $sortedQueryStringTmp .= '&' . $this->encode($key) . '=' . $this->encode($value);
        }

        $stringToSign = "GET&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));
        $sign         = base64_encode(hash_hmac('sha1', $stringToSign, $this->accessKeySecret . '&', true));
        $signature    = $this->encode($sign);

        // 执行URL
        $url = ($security ? 'https' : 'http') . "://{$this->domain}/?Signature={$signature}{$sortedQueryStringTmp}";

        try
        {
            $content = $this->fetchContent($url);

            return json_decode($content);
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }
    }

    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);

        return $res;
    }

    private function fetchContent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'x-sdk-client' => 'php/2.0.0'
        ));

        if (substr($url, 0, 5) == 'https')
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $rtn = curl_exec($ch);

        if ($rtn === false)
        {
            //trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
            return $rtn;
        }
        curl_close($ch);

        return $rtn;
    }
}
