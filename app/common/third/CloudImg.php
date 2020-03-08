<?php

namespace app\common\third;

use think\facade\Request;

/**
 * 内部图像服务器
 */
class CloudImg extends CloudSignature
{
    private $_domain          = '';  // 域名
    private $_accessKeyId     = ''; // Access Key ID
    private $_accessKeySecret = ''; // Access Access Key Secret

    public function __construct($config = array())
    {
        $this->_domain          = $config['domain'];
        $this->_accessKeyId     = $config['access_key'];
        $this->_accessKeySecret = $config['access_secret'];
    }

    /**
     * 上传图片
     *
     * @param array       $param    参数列表
     * @param string|null $path     文件路径
     * @param bool        $security 是否采用安全模式
     *
     * @return array|mixed
     */
    public function Upload(array $param = [], string $path = null, bool $security = false)
    {
        $curl = $this->CurlInit($param);

        // 其他参数放入请求中
        $url = ($security ? 'https' : 'http') . "://" . $this->_domain . '/api/v1/image/upload';

        //2.0 执行上传
        $curl->post($url, array_merge($param, ['file' => curl_file_create($path)]));
        if (!$curl->error)
        {
            return object2array($curl->response);
        }

        $rtn = [
            'code' => \EC::FILE_UPLOAD_ERROR,
            'msg'  => $curl->errorMessage,
        ];
        $curl->close();

        return $rtn;
    }

    /**
     * 图像裁剪
     *
     * @param array $param    参数列表
     * @param bool  $security 是否采用安全模式
     *
     * @return array|mixed
     */
    public function Mogr(array $param = [], bool $security = false)
    {
        $curl = $this->CurlInit($param);

        // 其他参数放入请求中
        $url = ($security ? 'https' : 'http') . "://" . $this->_domain . '/api/v1/image/mogr';

        //2.0 执行上传
        $curl->get($url, $param);
        if (!$curl->error)
        {
            return object2array($curl->response);
        }

        $rtn = [
            'code' => \EC::FILE_FRAME_ERROR,
            'msg'  => $curl->errorMessage,
        ];
        $curl->close();

        return $rtn;
    }


    /**
     * Curl 初始化
     *
     * @param array $param
     *
     * @return \Curl\Curl
     */
    protected function CurlInit(array $param = [])
    {
        try
        {
            $curl = new \Curl\Curl();

            //1.0 签名
            $signatureParms              = $this->params_signature($this->_accessKeyId);
            $signatureParms['signature'] = $this->signature($this->_accessKeySecret,
                array_merge($signatureParms, $param));

            // 放入 Header 中
            $curl->setHeaders($signatureParms);

            // set userAgent
            $userAgent = Request::server('HTTP_USER_AGENT');
            if ($userAgent)
                $curl->setUserAgent($userAgent);

            // set session id
            $curl->setCookies($_COOKIE);

            //2.0 设置超时时间
            $curl->setConnectTimeout(10);

            return $curl;
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }
    }
}
