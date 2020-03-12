<?php
namespace app\common\third\pay;

/**
 * 环迅支付
 * @package pay
 */
class Ips extends Base
{
    //接口地址
    protected const API_URL     = [
        //Web 网关（快捷支付）生产环境
        "payment"   => "https://newpay.ips.com.cn/psfp-entry/gateway/payment.do",
        //H5 网关（快捷支付）生产环境
        "paymenth5" => "https://mobilegw.ips.com.cn/psfp-mgw/paymenth5.do",
        //网银
        "gateway"   => "https://newpay.ips.com.cn/psfp-entry/gateway/payment.do",
    ];
    protected const NOTIFY_ADDR = "/api/pay/ipsNotify"; //异步通知地址
    protected const LOCATION    = "http://www.yadingtongye.com/ips_brige.html"; //中转站地址
    protected const MER_CODE    = "210200"; //商户编号
    protected const ACCOUNT     = "2102000011"; //交易账号
    protected const KEY         = "PDPgRqA1Vox8mGV6ZZUnALyKQYKTqptrJsFvTPOX1lfXp9hfwsG22BCJb8RBn97wLSx8Wq7BdTl3JVALwykPlnz7FwehYMVSjwfQvOtm5QqwpsZoyzkAKVQYSBshn3Sg"; //MD5证书

    protected $sign          = null; //签名
    protected $notifyUrl     = null; //异步通知地址
    protected $requestHeader = null; //请求头
    protected $requestBody   = null; //请求主体


    public function __construct()
    {
        $this->notifyUrl = (empty($_SERVER['HTTP_X_CLIENT_PROTO']) ? 'http' : $_SERVER['HTTP_X_CLIENT_PROTO']).'://'.$_SERVER["HTTP_HOST"].self::NOTIFY_ADDR;
    }

    /**
     * 下单
     * @author 王崇全
     * @date
     * @param string $orderNo   订单编号（不能重复）
     * @param float  $amount    金额（元，精确到分）
     * @param string $payType   充值渠道 payment,快捷；paymenth5，快捷H5;gateway,网关
     * @param string $returnUrl 回跳地址
     * @param bool   $isHtml    true，生成完整的html字符串；false，返回html所需的必要元素
     * @return array|string
     *                          ["formAction"=>提交地址,"inputName"=>input的name,"inputValue"=>input的value]
     */
    public function prePay(string $orderNo, float $amount, string $payType, string $returnUrl, bool $isHtml = true)
    {
        try {
            $res = $this->setParams($orderNo, $amount, $payType, $returnUrl)
                        ->setSign()
                        ->setPostDate()
                        ->makeHtmlStr($isHtml);
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
     * @return void
     * @throws \Exception
     */
    public function getNotifyData()
    {
        //接收原始数据
        $this->reData = $_REQUEST['paymentResult'] ?? null;
        if (!$this->reData) {
            $this->log("未收到数据");
            die("error");
        }

        $this->log($this->reData);

        $raw = $this->reData;

        //转为数组
        $this->reData = $this->xmlToArray($this->reData);
        if (!$this->reData) {
            $this->log("未收到有效数据");
            die("error");
        }
        $this->reData = $this->reData["GateWayRsp"];

        //保存待签名的字符
        $this->toSignStr = $this->subStr("<body>", "</body>", $raw);
    }


    /**
     * 组织请求参数
     * @author 王崇全
     * @date
     * @param string $orderNo   订单编号（不能重复）
     * @param float  $amount    金额（元，精确到分）
     * @param string $payType   充值渠道 payment,PC快捷；paymenth5,移动快捷；gateway,网银支付
     * @param string $returnUrl 回跳地址
     * @throws \Exception
     * @return $this
     */
    protected function setParams(string $orderNo, float $amount, string $payType, string $returnUrl)
    {
        switch ($payType) {
            case "payment":
                $this->apiUrl = self::API_URL["payment"];
                break;
            case "paymenth5":
                $this->apiUrl = self::API_URL["paymenth5"];
                break;
            case "gateway":
                $this->apiUrl = self::API_URL["gateway"];
                break;
            default:
                throw new \Exception("支付方式错误");
                break;
        }

        $this->params = [
            "MerBillNo"       => $orderNo,
            "GatewayType"     => "01",
            "Date"            => date("Ymd"),
            "CurrencyType"    => "156",
            "Amount"          => sprintf("%.2f", $amount),
            "Lang"            => "",
            "Merchanturl"     => $returnUrl,
            "FailUrl"         => "",
            "Attach"          => "",
            "OrderEncodeType" => "5",
            "RetEncodeType"   => "17",
            "RetType"         => "1",
            "ServerUrl"       => $this->notifyUrl,
            "BillEXP"         => "",
            "GoodsName"       => "Goods",
            "IsCredit"        => "",
            "BankCode"        => "",
            "ProductType"     => "",
        ];

        $this->requestBody = $this->arr2xml($this->params, "body", false);

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
        $this->sign = $this->makeSign($this->requestBody);

        return $this;
    }

    /**
     * 验证签名
     * @author 王崇全
     * @date
     * @return void
     * @throws \Exception
     */
    public function checkSign()
    {
        if ($this->makeSign($this->toSignStr) !== $this->reData["head"]["Signature"]) {
            $this->log("签名验证失败");
            die("error");
        }
    }

    /**
     * getRes
     * @author 王崇全
     * @date
     * @return array
     */
    public function getRes()
    {
        return [
            "isSuccess" => ($this->reData["head"]["RspCode"] === "000000") && ($this->reData["body"]["Status"] === "Y"),
            "orderNo"   => $this->reData["body"]["MerBillNo"],
            "amount"    => (float)$this->reData["body"]["Amount"],
            "Msg"       => $this->reData["body"]["Msg"],
        ];
    }

    /**
     * 设置要发送的数据
     * @author 王崇全
     * @date
     * @return $this
     */
    protected function setPostDate()
    {
        $this->requestHeader = $this->arr2xml([
            "Version"   => "v1.0.0",
            "MerCode"   => self::MER_CODE,
            "MerName"   => "",
            "Account"   => self::ACCOUNT,
            "MsgId"     => "",
            "ReqDate"   => date("YmdHis"),
            "Signature" => $this->sign,
        ], "head", false);

        $this->postData = "<Ips><GateWayReq>".$this->requestHeader.$this->requestBody."</GateWayReq></Ips>";

        return $this;
    }

    /**
     * 生成html
     * @author 王崇全
     * @date
     * @param bool $isHtml true，生成完整的html字符串；false，返回html所需的必要元素
     * @return array|string
     *                     ["formAction"=>提交地址,"inputName"=>input的name,"inputValue"=>input的value]
     */
    protected function makeHtmlStr(bool $isHtml = true)
    {
        if ($isHtml) {

            return <<<html
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>跳转中...</title>
</head>
<body>
    正在跳转至支付平台...
    <form id='ipspaysubmit' name='ipspaysubmit' method='post' action='{$this->apiUrl}'>
        <input type='hidden' name='pGateWayReq' value="{$this->postData}"/>
        <input type='submit' style='display:none;'>
    </form>
</body>
<script>document.forms['ipspaysubmit'].submit();</script>
</html>
html;
        }

        return [
            "payUrl"         => self::LOCATION."?"."action={$this->apiUrl}&pGateWayReqValue={$this->postData}",
            "formAction"     => $this->apiUrl,
            "formInputName"  => "pGateWayReqValue",
            "formInputValue" => "$this->postData",
        ];
    }


    /**
     * 制作签名
     * @author 王崇全
     * @date
     * @param string $requestBody 请求参数
     * @return string 签名
     * @throws \Exception
     */
    private function makeSign(string $requestBody)
    {
        if (!$requestBody) {
            throw new \Exception("参数为空");
        }

        $this->toSignStr = $requestBody.self::MER_CODE.self::KEY;

        return md5($this->toSignStr);
    }

}