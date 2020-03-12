<?php
namespace app\common\event;

use think\facade\Request;

//定义日志的Id号
$GLOBALS['g_logs_insert'] = false;
$GLOBALS['g_logs_opid']   = guid();
$GLOBALS['g_logs_optype'] = LOGOP_OP_TYPE_ADD;

//定义异步回调
$GLOBALS['g_callback'] = [];

/**
 * -应用初始化
 */
class AppInit
{
    public function handle()
    {
        //Todo: 该地方性能有待优化
        $this->ParsePostData();

        //防sql注入与xss攻击
        $webScan = new \Webscan();
        if ($webScan->Check())
        {
            return \think\Response::create(['code' => \EC::PARAM_SAFEERROR,
                                     'msg'  => \EC::GetMsg(\EC::PARAM_SAFEERROR)],
                'jsonp', \EC::API_ERR);
        }

        // ***关闭原生错误提示, 错误信息可以从tp5的日志中查看
        ini_set('display_errors', 'Off');

        // ***float和double型数据序列化存储时的精度(有效位数，-1表示使用实际值)
        ini_set('serialize_precision', '-1');

        //跨域相关处理
        $this->Cors();
    }

    // +--------------------------------------------------------------------------
    // |  私有方法
    // +--------------------------------------------------------------------------
    /**
     * 解析 POST 消息主体
     * POST 的 ContentType 为【application/x-www-form-urlencoded】 或 【multipart/form-data】 时，PHP可自动解析至$_POST中
     * 但 ContentType 为其他情况时【text/plain】,PHP目前不会解析
     * 本方法用来解析 POST 请求的消息主体
     *
     * @return void
     */
    protected function ParsePostData()
    {
        //1.0 请求方式
        $requestMethod = Request::server('REQUEST_METHOD') ?? '';
        if (!($requestMethod == 'POST'))
        {
            return;
        }

        //2.0 数据格式
        $ContentType = Request::server('CONTENT_TYPE') ?? null;

        //2.1 跳过 php 支持的数据格式
        if (isset($ContentType))
        {
            $noParse = [
                'multipart/form-data',
                'application/x-www-form-urlencoded',
            ];

            foreach ($noParse as $val)
            {
                if (stripos($ContentType, $val) !== false)
                {
                    return;
                }
            }
        };

        //2.2 原始数据
        if (!($data = file_get_contents('php://input')))
        {
            return;
        }

        if (isset($ContentType))
        {
            switch (strtolower($ContentType))
            {
                case 'application/json':
                    $json     = json_decode($data, true);
                    $_POST    = $this->ArraysMerge($_POST, $json);
                    $_REQUEST = $this->ArraysMerge($_REQUEST, $json);
                    break;
                case 'text/plain':
                    $this->ParseTextPlain($data);
                    break;
                default:
                    return;
                    break;
            }
        }
        else
        {
            $this->ParseTextPlain($data);
        }
    }

    /**
     * 跨域处理
     */
    protected function Cors()
    {
        //【0】 跨域向IE8写入cookie
        if (is_ie())
        {
            // P3P header允许跨域访问隐私数据
            header("P3P: CP='CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR'");
        }

        //【3.0】 CORS 跨域
        $request = Request();

        // webuploader 分片上传时，首先会发送该请求
        if ($request->isOptions())
        {
            crossdomain_cors();
            header('HTTP/1.1 204 empty');
            return;
        }

        // POST请求
        if ($request->isPost())
        {
            crossdomain_cors();
        }
    }

    private function ParseTextPlain(string $textPlain)
    {
        $textPlainArr = @explode('&', $textPlain);
        if (!$textPlainArr)
        {
            return;
        }

        foreach ($textPlainArr as $value)
        {
            $requestArr = explode('=', $value);

            //参数值
            $content = urldecode(isset($requestArr[1]) ? ($requestArr[1]) : '');

            //参数名
            $name = urldecode($requestArr[0]);

            $arr = [];
            if (!preg_match_all('/\[(.*?)\]/is', $name, $arr))
            { //非数组参数

                $_POST[$name]    = $content;
                $_REQUEST[$name] = $content;

                continue;
            }

            //数组参数
            $keyName1 = substr($name, 0, strpos($name, '['));
            array_unshift($arr[1], $keyName1);

            $tmpArr = $content;
            while (!is_null($key = array_pop($arr[1])))
            {
                $tmpArr = [
                    $key => $tmpArr,
                ];
            }

            $_POST    = $this->ArraysMerge($_POST, $tmpArr);
            $_REQUEST = $this->ArraysMerge($_REQUEST, $tmpArr);
        }
    }

    /**
     * MergeArrays
     *
     * @param array $arr1
     * @param array $arr2
     *
     * @return array
     * @author jiangjiaxiong
     * @date
     */
    private function ArraysMerge(array $arr1, array $arr2)
    {
        foreach ($arr2 as $key => $value)
        {
            if (is_array($value) && array_key_exists($key, $arr1))
            {
                $arr1[$key] = $this->ArraysMerge($arr1[$key], $arr2[$key]);
            }
            else
            {
                $arr1[$key] = $value;
            }

        }

        return $arr1;
    }
}