<?php

namespace app\common\third;

use string\P4String;

/**
 * 产品注册服务
 */
class CloudAuth
{
    private static $_sDomain = 'http://auth3.boolongo.com';

    private static $_errorMap = array(
        200 => \EC::SUCCESS,
        401 => \EC::PARAM_ERROR,
        420 => \EC::AUTH_NOT_EXIST,
        421 => \EC::AUTH_MACHINESCODE_EXIST,
        423 => \EC::AUTH_ACTIVECODE_ERROR,
        424 => \EC::AUTH_EXPIRE_ERROR,
        440 => \EC::DB_OPERATION_ERROR,
        460 => \EC::AUTH_RULE_NOT_EXIST,
        461 => \EC::AUTH_RULE_EXIST,
    );

    public function __construct()
    {

    }

    /**
     * 添加授权
     *
     * @param $machinecode
     * @param $activecode
     * @param $registerto
     *
     * @return mixed
     */
    public function AddAuth($machinecode, $activecode, $registerto)
    {
        return $this->Request('/api/auth/addExpire', [
            'productname'    => AUTH_PRODUCT_NAME,
            'productversion' => AUTH_PRODUCT_VERSION,
            'machinecode'    => $machinecode,
            'activecode'     => $activecode,
            'registerto'     => $registerto,
        ]);
    }

    /**
     * 获取授权
     *
     * @param $machinecode
     * @param $resRcd
     *
     * @return int|mixed
     */
    public function GetAuth($machinecode)
    {
        return $this->Request('/api/auth/info', [
            'productname' => AUTH_PRODUCT_NAME,
            'machinecode' => $machinecode,
        ]);
    }

    /**
     * 请求
     *
     * @param string $url    请求URL
     * @param array  $params 请求参数
     *
     * @return array|mixed|null|string
     */
    private function Request(string $url, array $params = [])
    {
        try
        {
            $query_string = http_build_query($params);

            $opts = array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  => "Content-Type: text/xml\r\n" . 'Authorization: Basic ' . base64_encode('admin:abc_123') . "\r\n",
                    'timeout' => 30,
                ),
            );

            $requestAPi = self::$_sDomain . $url . '?' . $query_string;
            $content    = file_get_contents($requestAPi, false, stream_context_create($opts));
            $jsonArr    = P4String::jsondecode($content);

            if (!isset($jsonArr) || !isset($jsonArr['code']))
            {
                return [
                    'code' => \EC::AUTH_NOT_EXIST,
                    'msg'  => '授权信息未找到'
                ];
            }

            $sorCode = $jsonArr['code'];
            if(array_key_exists($sorCode, self::$_errorMap))
                $jsonArr['code'] = self::$_errorMap[$sorCode];

            return $jsonArr;
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }
    }
}
