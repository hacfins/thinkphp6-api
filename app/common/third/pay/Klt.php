<?php
namespace app\common\third\pay;

/**
 * Class 开联通支付
 * @package Common\Model
 */
class Klt extends Baifu
{
    const  INPUT_CHARSET = 1;//字符集 (1代表UTF-8、2代表GBK、3代表GB2312；)
    const  VERSION       = "v1.0";//网关接收支付请求接口版本
    const  LANGUAGE      = 1;//网关页面显示语言种类,1代表简体中文
    const  SIGN_TYPE     = 0;//签名类型
    //    const  MERCHANT_ID    = "100020091219001";//商户号
    const  MERCHANT_ID = "108810171117001";//商户号
    //    const  MERCHANT_KEY   = "1234567890";//用于计算signMsg的key值
    const  MERCHANT_KEY   = "hHDfger47rttEgsdrurgw6ey";//用于计算signMsg的key值
    const  ORDER_CURRENCY = 156;//订单金额币种类型 (156代表人民币、840代表美元、344代表港币)
    const  PAY_TYPE       = 0;//支付方式
    const  TARGET         = "https://mobile.openepay.com/mobilepay/index.do";//测试环境：http://opsweb.koolyun.cn/mobilepay/index.do ; 生产环境：https://mobile.openepay.com/mobilepay/index.do

    /**
     * pay
     * 程序会在本方法内终止运行
     * @author 王崇全
     * @date
     * @param string $orderNo     单号
     * @param string $productName 商品名
     * @param string $orderAmount 金额 (元)
     * @param string $receiveUrl  结果通知地址
     * @param string $pickupUrl   回跳地址
     * @return void
     * @throws \Exception
     */
    public static function pay($orderNo, $productName, $orderAmount, $receiveUrl, $pickupUrl)
    {
        if (!($orderNo && $productName && $orderAmount && $receiveUrl && $pickupUrl)) {
            throw new \Exception("参数错误");
        }

        $orderAmount *= 100;//分->元

        $now = date("YmdHis");

        $orderDatetime = $now;//商户订单提交时间

        $data = [
            "inputCharset"  => self::INPUT_CHARSET,
            "pickupUrl"     => $pickupUrl,
            "receiveUrl"    => $receiveUrl,
            "version"       => self::VERSION,
            "language"      => self::LANGUAGE,
            "signType"      => self::SIGN_TYPE,
            "merchantId"    => self::MERCHANT_ID,
            "orderNo"       => $orderNo,
            "orderAmount"   => $orderAmount,
            "orderCurrency" => self::ORDER_CURRENCY,
            "orderDatetime" => $orderDatetime,
            "productName"   => $productName,
            "payType"       => self::PAY_TYPE,
        ];

        $dataStr = "";
        foreach ($data as $k => $v) {
            $k = trim($k);
            $v = trim($v);

            $dataStr .= "&{$k}={$v}";
        }
        $dataStr = ltrim($dataStr, "&");

        $signMsg = strtoupper(md5($dataStr."&key=".self::MERCHANT_KEY));//签名字符串

        $action = self::TARGET;

        $str = <<<EOF
<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta http-equiv="Expires" CONTENT="0">
	<meta http-equiv="Cache-Control" CONTENT="no-cache">
	<meta http-equiv="Pragma" CONTENT="no-cache">
	<title>正在跳转至支付...</title>
</head>
<body>
正在跳转至支付页面...
<form id="formPay" action="{$action}" method="post">
	<input type="hidden" name="inputCharset" id="inputCharset" value="{$data['inputCharset']}"/>
	<input type="hidden" name="pickupUrl" id="pickupUrl" value="{$data['pickupUrl']}"/>
	<input type="hidden" name="receiveUrl" id="receiveUrl" value="{$data['receiveUrl']}"/>
	<input type="hidden" name="version" id="version" value="{$data['version']}"/>
	<input type="hidden" name="language" id="language" value="{$data['language']}"/>
	<input type="hidden" name="signType" id="signType" value="{$data['signType']}"/>
	<input type="hidden" name="merchantId" id="merchantId" value="{$data['merchantId']}"/>
	<input type="hidden" name="orderNo" id="orderNo" value="{$data['orderNo']}"/>
	<input type="hidden" name="orderAmount" id="orderAmount" value="{$data['orderAmount']}"/>
	<input type="hidden" name="orderCurrency" id="orderCurrency" value="{$data['orderCurrency']}"/>
	<input type="hidden" name="orderDatetime" id="orderDatetime" value="{$data['orderDatetime']}"/>
	<input type="hidden" name="productName" id="productName" value="{$data['productName']}"/>
	<input type="hidden" name="payType" value="{$data['payType']}"/>
	<input type="hidden" name="signMsg" id="signMsg" value="{$signMsg}"/>
</form>
</body>
<script>document.getElementById("formPay").submit();</script>
</html>
EOF;

        die($str);
    }

    /**
     * 获取通知数据（未实现）
     * @author 王崇全
     * @date
     * @return array
     */
    public static function getData()
    {
        //原始报文
        $dataRaw = file_get_contents("php://input");

        //保存日志
        self::log($dataRaw);

        /* issuerId=&orderNo=5a13a8a38a461&payDatetime=20171121131139&ext1=&mchtOrderId=201711211311026954&payResult=1&orderAmount=2&ext2=&signMsg=81B6657B0BC4A57B2F1F7991955BF3E1&signType=0&payType=0&merchantId=100020091219001&language=1&orderDatetime=20171121121635&version=v1.0 */
        if (!$dataRaw) {
            die("未收到任何POST报文");
        }
        $data     = explode("&", $dataRaw);
        $postData = [];
        foreach ($data as $v) {
            $v                 = explode("=", $v);
            $postData[ $v[0] ] = $v[1];
        }

        /*
            merchantId	商户号	30	不可空	数字串，与提交订单时的商户号保持一致
            version	网关返回支付结果接口版本	10	不可空	固定选择值：v1.0
            language	网，页显示语言种类	2	可空	1代表简体中文、2代表繁体中文、3代表英文
            signType	签名类型	2	不可空	固定选择值：0、1，与提交订单时的签名类型保持一致
            payType	支付方式	2	可空	字符串，返回用户在实际支付时所使用的支付方式
            issuerId	发卡方机构代码	8	可空	字符串，返回用户在实际支付时所使用的发卡方机构代码
            mchtOrderId	开联订单号	50	不可空	字符串，开联订单号
            orderNo	商户订单号	50	不可空	字符串，与提交订单时的商户订单号保持一致
            orderDatetime	商户订单提交时间	14	不可空	数字串，与提交订单时的商户订单提交时间保持一致
            orderAmount	商户订单金额	10	不可空	整型数字，金额与币种有关
            如果是人民币，则单位是分，即10元提交时金额应为1000
            如果是美元，单位是美分，即10美元提交时金额为1000
            payDatetime	支付完成时间	14	不可空	日期格式：yyyyMMDDhhmmss，例如：20121116020101
            ext1	扩展字段1	128	可为空	按照原样返回给商户
            ext2	扩展字段2	128	可为空	按照原样返回给商户
            payResult	处理结果	2	不可空	1：支付成功
            商户可以通过查询接口查询订单状态。
            signMsg	签名字符串	1024	不可空	以上所有非空参数按上述顺序与密钥组合，经加密后生成该值。
         */
        if (!$postData) {
            die("未收到与文档一致的报文");
        }

        //验证商户号
        if ($postData['merchantId'] !== Klt::MERCHANT_ID) {
            die("商户号错误");
        }

        /*验证签名*/

        //参与签名的参数
        $toSignParas = [
            "merchantId",
            "version",
            "language",
            "signType",
            "payType",
            "issuerId",
            "mchtOrderId",
            "orderNo",
            "orderDatetime",
            "orderAmount",
            "payDatetime",
            "ext1",
            "ext2",
            "payResult",
        ];

        $toSignStr = "";
        foreach ($toSignParas as $toSignPara) {
            foreach ($postData as $k => $v) {
                $k = trim($k);
                $v = trim($v);
                if ($k != $toSignPara || $v === "") {
                    continue;
                }
                $toSignStr .= "{$k}={$v}&";
            }
        }
        $toSignStr = $toSignStr."key=".Klt::MERCHANT_KEY;

        $sign = strtoupper(md5($toSignStr));
        if ($sign !== $postData['signMsg']) {
            die("签名验证失败");
        }

        return $postData;
    }

}