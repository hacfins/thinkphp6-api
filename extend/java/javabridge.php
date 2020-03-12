<?php

namespace java;

use think\facade\Session;

class JavaBridge
{
    public const IP            = "tdxwt.hfzq.com.cn"; //券商交易服务器IP
    public const PORT          = 8081; //券商交易服务器端口
    public const VERSION       = "6.00"; //通达信客户端的版本号  恒为6.00
    public const YYB_ID        = 0; //营业部代码
    public const ACCOUNT_NO    = "200300004063"; //完整的登录账号, 券商一般使用资金账户或客户号
    public const TRADE_ACCOUNT = "200300004063"; //交易账号, 一般与登录账号相同,请登录券商通达信软件, 查询股东列表, 股东列表内的资金账号就是交易账号,具体查询方法请见网站"热点问答"栏
    public const JY_PWD        = "132720"; //交易密码
    public const TX_PWD        = "132720"; //通讯密码, 与交易密码相同

    public const GDDM_SH = "A408977786"; //上海股东代码
    public const GDDM_SZ = "0258211409"; //深圳股东代码

    public $_tdx = null; //通达信 java 对象

    /**
     * JavaBridge constructor.
     * @throws \Exception
     */
    public function __construct()
    {

        //检查操作系统类型 //受通达信影响
        if ("WIN32" != PHP_OS && "WINNT" != PHP_OS) {
            throw new \Exception("本接口仅支持Windows7");
        }

        //此处端口与第五步的端口对应
        define("JAVA_HOSTS", "127.0.0.1:8080");

        define("JAVA_LOG_LEVEL", 2);

        //将第一步的下载的Java.inc与当前编辑的php文件放在同一层目录

        try {
            require_once "Java.inc";
            java_set_file_encoding("UTF-8");

        } catch (\Exception $e) {
            throw new \Exception("请检查JavaBridge进程是否已正常运行", $e->getCode());
        }

        //Java类的第一个参数是JAVA开发的类的名字包含包路径，
        //路径表示按JAVA里导入包的格式。
        //如果JAVA下的类需要使用构造函数，可以在使用第二个参数

        try {

            $this->_tdx = new \Java("com.rmkj.LoadLibrary", __DIR__."/lib/trade.dll");

        } catch (\JavaException $ex) {
            echo $ex;
            echo "<br>\n";
        }

        // print_r(java_inspect($this->_tdx));
    }


    /**
     * 打开通达信
     * @author 王崇全
     * @date   2019/1/22 12:35
     * @return void
     */
    public function open()
    {
        $this->_tdx->OpenTdx();
    }

    /**
     * 关闭通达信
     * @author 王崇全
     * @date   2019/1/22 12:36
     * @return void
     */
    public function close()
    {
        $this->_tdx->CloseTdx();

        Session::delete("tdx_client_id");
    }

    /**
     * 交易账户登录
     * @author 王崇全
     * @date   2019/1/22 12:42
     * @return int 客户端id, 失败时返回-1
     * @throws
     */
    public function logon()
    : int
    {
        try {
            $res = java_values($this->_tdx->Logon(self::IP, self::PORT, self::VERSION, self::YYB_ID, self::ACCOUNT_NO,
                self::TRADE_ACCOUNT, self::JY_PWD, self::TX_PWD));
        } catch (\JavaException $ex) {
            echo $ex;
            echo "<br>\n";
        }

        if ($res[0] == -1) {
            throw new \Exception("客户端登录失败:".$res[1]);
        }

        Session::set("tdx_client_id", $res[0]);

        return $res[0];
    }

    /**
     * queryData
     * @author 王崇全
     * @date   2019/1/23 14:29
     * @param int $category 0资金 1股份 2当日委托 3当日成交 4可撤单 5股东代码 6融资余
     *                      额 7融券余额 8可融证券
     * @return array
     * @throws \Exception
     */
    public function queryData(int $category)
    : array {
        $clientId = $this->getClientId();

        try {
            $res = $this->_tdx->QueryData($clientId, $category);
            $res = java_values($res);
        } catch (\JavaException $ex) {
            echo $ex;
            echo "<br>\n";
        }

        if ($res[1]) {
            throw new \Exception("查询失败:".$res[1]);
        }

        return $this->str2arr($res[0]);
    }


    /**
     * 查询历史数据
     * @author 王崇全
     * @date   2019/1/23 17:00
     * @param int    $category  0历史委托 1历史成交 2交割单
     * @param String $startDate 示开始日期，格式为yyyyMMdd,比如2014年3月1日为 20140301
     * @param String $endDate   结束日期，格式为yyyyMMdd,比如2014年3月1日为 20140301
     * @return array
     * @throws \Exception
     */
    public function queryHistoryData(int $category, String $startDate, String $endDate)
    : array {
        $clientId = $this->getClientId();

        try {
            $res = $this->_tdx->QueryHistoryData($clientId, $category, $startDate, $endDate);
            $res = java_values($res);
        } catch (\JavaException $ex) {
            echo $ex;
            echo "<br>\n";
        }

        if ($res[1]) {
            throw new \Exception("查询失败:".$res[1]);
        }

        return $this->str2arr($res[0]);
    }

    /**
     * 发送订单
     * @author 王崇全
     * @date   2019/1/23 17:14
     * @param int    $category  0买入 1卖出 2融资买入 3融券卖出 4买券还券 5卖券还款 6现券还券
     * @param int    $priceType 报价方式
     *                          0上海限价委托(通常) 深圳限价委托
     *                          1(市价委托)深圳对方最优价格
     *                          2(市价委托)深圳本方最优价格
     *                          3(市价委托)深圳即时成交剩余撤销
     *                          4(市价委托)上海五档即成剩撤 深圳五档即成剩撤
     *                          5(市价委托)深圳全额成交或撤销
     *                          6(市价委托)上海五档即成转限价
     * @param String $zqdm      证券代码(股票代码)
     * @param float  $price     委托价格
     * @param int    $quantity  委托数量
     * @return array
     * @throws \Exception
     */
    public function sendOrder(int $category, int $priceType, String $zqdm, float $price, int $quantity)
    : array {
        $clientId = $this->getClientId();

        //股东代码
        $gddm = "";
        if (stripos($zqdm, "sz") !== false) {
            $gddm = self::GDDM_SZ;
            $zqdm = str_replace("sz", "", $zqdm);
        } elseif (stripos($zqdm, "sh") !== false) {
            $gddm = self::GDDM_SH;
            $zqdm = str_ireplace("sh", "", $zqdm);
        }

        try {
            $res = $this->_tdx->SendOrder($clientId, $category, $priceType, $gddm, $zqdm, $price, $quantity);
            $res = java_values($res);
        } catch (\JavaException $ex) {
            echo $ex;
            echo "<br>\n";
        }

        if ($res[1]) {
            throw new \Exception("下单失败:".$res[1]);
        }

        return $this->str2arr($res[0]);
    }

    /**
     * 撤销订单
     * @author 王崇全
     * @date   2019/1/24 11:33
     * @param String $exchangeID 交易所ID， 上海1，深圳0(招商证券普通账户深圳是2)
     * @param String $hth        要撤的目标委托的编号
     * @return array
     * @throws \Exception
     */
    public function cancelOrder(String $exchangeID, String $hth)
    : array {

        $clientId = $this->getClientId();

        try {
            $res = $this->_tdx->CancelOrder($clientId, $exchangeID, $hth);
            $res = java_values($res);
        } catch (\JavaException $ex) {
            echo $ex;
            echo "<br>\n";
        }

        if ($res[1]) {
            throw new \Exception("撤单失败:".$res[1]);
        }

        return $this->str2arr($res[0]);
    }


    /**
     * 获取 ClientID
     * @author 王崇全
     * @date   2019/1/23 14:26
     * @return int ClientID
     * @throws \Exception
     */
    private function getClientId()
    : int
    {

        $res = Session::get("tdx_client_id");
        if (!$res) {
            throw new \Exception("请先登录");
        }

        return $res;
    }

    /**
     * 解析返回结果
     * @author 王崇全
     * @date   2019/1/23 15:18
     * @param string $str
     * @return array
     */
    private function str2arr(string $str)
    {
        $list = explode("\n", trim($str));
        foreach ($list as $k => &$v) {
            $v = explode("\t", $v);
        }

        return $list;
    }

}


