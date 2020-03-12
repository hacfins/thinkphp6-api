<?php
namespace app\common\third\pay;


class Baifu extends Base
{
    protected const API_URL     = "http://pay.91baifu.cn/gateway/interface/pay.htm"; //接口地址
    protected const NOTIFY_ADDR = "/api/pay/baifuNotify"; //异步通知地址
    protected const PARTNER     = "C100024"; //商户编号
    protected const KEY         = "5ec46c3acdbdbff1423920c7274630f9"; //秘钥

    protected $sign      = null; //签名
    protected $notifyUrl = null; //异步通知地址


    public function __construct()
    {
        $this->apiUrl = self::API_URL;

        $this->notifyUrl = (empty($_SERVER['HTTP_X_CLIENT_PROTO']) ? 'http' : $_SERVER['HTTP_X_CLIENT_PROTO']).'://'.$_SERVER["HTTP_HOST"].self::NOTIFY_ADDR;

    }

    /**
     * 下单
     * @author 王崇全
     * @date
     * @param string $orderNo 订单编号（不能重复）
     * @param float  $amount  金额（元，精确到分）
     * @param string $payType 充值渠道 QQH5，QQH5支付；JDH5，JDH5支付
     * @return bool 下单是否成功
     */
    public function sendOrder(string $orderNo, float $amount, string $payType)
    {
        try {
            $res = $this->setParams($orderNo, $amount, $payType)
                        ->setSign()
                        ->setPostDate()
                        ->send()
                        ->getRes();
        } catch (\Exception $e) {
            $this->log($e->getMessage());
            $res = false;
        }

        return $res;
    }

    /**
     * 获取异步通知的数据
     * @author 王崇全
     * @date
     * @return array
     * [
     * "outOrderId"=>商户订单号,
     * "orderCode"=>平台订单号,
     * "partnerOrderStatus"=>订单状态,
     * "amount"=>订单金额,
     * "returnParam"=>回传参数,
     * ]
     * @throws \Exception
     */
    public function getNotifyData()
    {
        //接收数据
        $data = file_get_contents("php://input");
        if (!$data) {
            $this->log("未收到数据");
            die("error");
        }

        $this->log($data);

        $data = json_decode($data, true);
        if (!$data || empty($data)) {
            $this->log("未收到有效数据");
            die("error");
        }

        //验签
        $sign = $data["sign"];
        unset($data["sign"]);
        $mySign = $this->makeSign($data, false);
        if ($sign !== $mySign) {
            $this->log("异步通知验签失败");
            die("error");
        }

        //判断是否成功
        if ($data["partnerOrderStatus"] !== "SUCCESS") {
            $this->log("订单支付失败");
            die("error");
        }

        unset($data["apiCode"]);
        unset($data["inputCharset"]);
        unset($data["partner"]);
        unset($data["signType"]);
        unset($data["sign"]);

        return $data;
    }


    /**
     * 组织请求参数
     * @author 王崇全
     * @date
     * @param string $orderNo 订单编号（不能重复）
     * @param float  $amount  金额（元，精确到分）
     * @param string $payType 充值渠道 QQH5，QQH5支付；JDH5，JDH5支付
     * @return $this
     */
    protected function setParams(string $orderNo, float $amount, string $payType)
    {
        $this->params = [
            "apiCode"      => "YL-PAY",
            "inputCharset" => "UTF-8",
            "signType"     => "MD5",
            "partner"      => self::PARTNER,
            "outOrderId"   => $orderNo,
            "amount"       => $amount,
            "payType"      => $payType,
            "notifyUrl"    => $this->notifyUrl,
        ];

        return $this;
    }

    /**
     * 计算签名
     * @author 王崇全
     * @date
     * @return $this
     * @throws \Exception
     */
    protected function setSign()
    {
        //得到签名
        $this->sign = $this->makeSign($this->params, false);

        //补充签名这个参数
        $this->params["sign"] = $this->sign;

        return $this;
    }

    /**
     * 设置要发送的数据
     * @author 王崇全
     * @date
     * @return $this
     */
    protected function setPostDate()
    {
        $this->postData = http_build_query($this->params);

        return $this;
    }


    /**
     * 解析请求应答
     * $this->reData：[
     *     "apiCode"=>接口编码,
     *     "inputCharset"=>字符集,
     *     "orderCode"=>平台订单号,
     *     "outOrderId"=>商户订单号,
     *     "partner"=>商户号,
     *     "?"=>响应码, //0000 成功;  其他 失败
     *     "?"=>响应信息, //SUCCESS
     * ]
     * @author 王崇全
     * @date
     * @return string 付款URL
     * @throws \Exception
     */
    protected function getRes()
    {
        $this->reData = json_decode($this->reData, true);

        if (!$this->reData || empty($this->reData)) {
            throw new \Exception("未收到有效数据");
        }

        if ($this->reData["responseCode"] !== "0000") {
            return $this->reData["responseMsg"];
        }

        //验签
        $sign = $this->reData["sign"] ?? null;
        unset($this->reData["sign"]);
        $mySign = $this->makeSign($this->reData, true);

        if (!isset($sign)) {
            $this->reData = null;
            throw new \Exception($this->reData["responseMsg"]);
        }
        if ($sign !== $mySign) {
            $this->reData = null;
            throw new \Exception("返回值验签失败");
        }

        return $this->reData["qrCodeUrl"] ? $this->reData["qrCodeUrl"] : $this->reData["qrCodeImgUrl"];
    }


    /**
     * 制作签名
     * @author 王崇全
     * @date
     * @param array $params       请求参数
     * @param bool  $includeEmpty 空值是否参与签名
     * @return string 签名
     * @throws \Exception
     */
    private function makeSign(array $params, bool $includeEmpty)
    {
        if (empty($params)) {
            throw new \Exception("参数为空");
        }

        //参数名ASCII码从小到大排序（字典序）
        ksort($params);

        //URL键值对的格式
        $paramsStr = '';
        foreach ($params as $k => $v) {

            //参数的值为空不参与签名
            $v = trim($v);
            if (!$includeEmpty && ($v == '' || is_null($v))) {
                continue;
            }

            $paramsStr .= "&{$k}={$v}";
        }
        $paramsStr = ltrim($paramsStr, "&");

        //拼接上key
        $paramsStr .= self::KEY;

        return strtoupper(md5($paramsStr));
    }

}