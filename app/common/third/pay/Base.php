<?php
namespace app\common\third\pay;

abstract class Base
{
    protected $params    = null; //请求参数
    protected $toSignStr = null; //参与签名的字符串
    protected $postData  = null; //要发送的数据
    protected $reData    = null; //返回的数据
    protected $apiUrl    = null; //接口地址

    private const DEBUG = true; //调试开关

    /**
     * 发送支付下单请求
     * @author 王崇全
     * @date
     * @return $this
     */
    protected function curl()
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);//单位S 秒

        $res = curl_exec($ch);
        $this->log("发送的数据:".$this->postData);
        curl_getinfo($ch);

        if (curl_error($ch)) {
            $this->log("curl_http_error:".curl_error($ch));
        }

        curl_close($ch);

        $this->log("返回内容:".$res);

        return $res;
    }

    /**
     * 生成订单编号
     * @author 王崇全
     * @date
     * @return string 订单编号
     */
    public function makeOrderNo()
    {
        return date("YmdHis").uniqid();
    }

    /**
     * 获取只包含域名的网址
     * @author 王崇全
     * @date
     * @return string 域名
     * @throws \Exception
     */
    public function getMainUrl()
    {

        if (!isset($_SERVER["SERVER_PORT"]) || $_SERVER["SERVER_PORT"] == "80") {
            $_SERVER["SERVER_PORT"] = "";
        }

        $portStr = "";
        if ($_SERVER["SERVER_PORT"]) {
            $portStr = ":".$_SERVER["SERVER_PORT"];
        }

        return ($_SERVER["REQUEST_SCHEME"] ?? "http")."://".$_SERVER["SERVER_NAME"].$portStr;
    }

    /**
     * 数组转XML
     * @author 王崇全
     * @date
     * @param array  $data     数据
     * @param string $root     根节点名
     * @param bool   $withXml  是否带xml头
     * @param string $encoding 数据编码
     * @return string 编码后的xml字符
     */
    public function arr2xml(array $data, string $root = "xml", bool $withXml = true, string $encoding = 'utf-8')
    {
        $xml = '';

        if ($withXml) {
            $xml .= '<?xml version="1.0" encoding="'.$encoding.'"?>';
        }

        $xml .= '<'.$root.'>';
        $xml .= $this->xmlMain($data);
        $xml .= '</'.$root.'>';

        return $xml;
    }

    /**
     * 将XML转为array
     * @author 王崇全
     * @date
     * @param string $xml
     * @return array
     * @throws \Exception
     */
    public function xmlToArray(string $xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if (!$values) {
            throw new \Exception("xml解析失败");
        }

        return $values;
    }

    /**
     * 日志
     * @author 日志记录
     * @date
     * @param string $msg 日志内容
     * @return bool
     */
    public function log(string $msg)
    {
        if (!self::DEBUG) {
            return false;
        }

        $path = runtime_path(). 'log/_pay/';
        if (!is_dir($path)) {
            if (!mkdir($path, 0777)) {
                return false;
            }
        }

        return error_log(date("[H:i:s]").": ".$msg."\r\n", 3, $path.date("Ymd").'.log');
    }

    /**
     * 截取 $begin 和 $end 之間字符串
     * @param string $begin 开始字符串
     * @param string $end   结束字符串
     * @param string $str   需要截取的字符串
     * @return string
     */
    public function subStr($begin, $end, $str)
    {
        $b = (strpos($str, $begin));
        $c = (strpos($str, $end));

        return substr($str, $b, $c - $b + strlen($end));
    }

    /**
     * 数组转XML
     * @author 王崇全
     * @date
     * @param array $arr
     * @return string
     */
    private function xmlMain(array $arr)
    {

        $xml = "";
        foreach ($arr as $key => $val) {

            //字符转义问题
            global $toCDATA;
            $toCDATA = false;
            if ($val === "" || is_numeric($val)) {
                $toCDATA = false;
            } else {
                array_map(function ($v) use ($val)
                {
                    $pos = strpos($val, $v);
                    if ($pos !== false) {
                        global $toCDATA;
                        $toCDATA = true;
                    }
                }, [
                    ">",
                    "<",
                    "&",
                    "'",
                    "\"",
                ]);
            }

            if (!$toCDATA) {
                $xml .= "<".$key.">".$val."</".$key.">";
            } elseif (is_array($val) || is_object($val)) {
                $xml .= $this->xmlMain($val);
            } else {
                $xml .= "<".$key."><![CDATA[".$val."]]></".$key.">";
            }

            unset($toCDATA);
        }

        return $xml;
    }

    /**
     * getRes
     * @author 王崇全
     * @date
     * @return array ["isSuccess"=>是否成功,"orderNo"=>订单编号,"amount"=>金额,"Msg"=>消息]
     */
    public abstract function getRes();

}