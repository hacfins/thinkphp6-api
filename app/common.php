<?php
// +----------------------------------------------------------------------
// | 公共函数
// +----------------------------------------------------------------------

use think\facade\
{Cache, Cookie, Request
};

use Wechat\Loader;
use app\common\facade\
{
    Browser
};

use Carbon\Carbon;

/**
 * 抛出异常处理
 *
 * @param int  $code   异常代码 默认为200
 * @param null $msg    异常消息
 * @param bool $bTrace 异常类
 *
 * @throws Exception
 */
function E($code = \EC::SUCCESS, $msg = null, $bTrace = true)
{
    if (is_null($code))
    {
        $code = \EC::API_ERR;
    }
    if (!$msg)
    {
        $msg = \EC::GetMsg($code == 0 ? 200 : $code);
    }

    $e = $bTrace ? '\think\Exception' : '\app\common\exception\ResponseException';
    throw new $e($msg, $code);
}

// +----------------------------------------------------------------------
// | 字符串
// +----------------------------------------------------------------------
function guid()
{
    return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),
        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,
        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,
        // 48 bits for "node"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 *
 * @param string  $name 字符串
 * @param integer $type 转换类型
 *
 * @return string
 */
function parse_name($name, $type = 0)
{
    if ($type)
    {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name));
    }
    else
    {
        return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
    }
}

/**
 * 生成唯一邀请码
 *
 * @date
 *
 * @param int $lenth 邀请码长度
 *
 * @return string
 */
function create_invite_code(int $lenth = 4): string
{
    $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $rand = $code[rand(0, 25)]
        . strtoupper(dechex(date('m')))
        . date('d')
        . substr(time(), -5)
        . substr(microtime(), 2, 5)
        . sprintf('%02d', rand(0, 99));
    for (
        $a = md5($rand, true),
        $s = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        $d = '',
        $f = 0;
        $f < $lenth;
        $g = ord($a[$f]),
        $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],
        $f++
    )
        ;

    return $d;
}

/**
 * 生成订单编号
 *
 * @date
 * @return string
 */
function makeOrderNo()
{
    return date("YmdHis") . uniqid();
}

function s2b($str)
{
    //1.列出每个字符
    $arr = preg_split('/(?<!^)(?!$)/u', $str);

    //2.unpack字符
    foreach ($arr as &$v)
    {
        $temp = unpack('H*', $v);
        $v    = base_convert($temp[1], 16, 2);
        unset($temp);
    }

    return join(' ', $arr);
}

function b2s($str)
{
    $arr = explode(' ', $str);
    foreach ($arr as &$v)
    {
        $v = pack("H" . strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
    }

    return join('', $arr);
}


// +----------------------------------------------------------------------
// | 时间
// +----------------------------------------------------------------------
/**
 * 获取毫秒精度的时间戳
 *
 * @date
 * @return mixed
 */
function time_m()
{
    return array_sum(explode(' ', microtime()));
}

/**
 * 毫秒格式化 '00:00:00.456'
 *
 * @param int $time 毫秒
 *
 * @return string
 */
function microtime_format(int $time)
{
    date_default_timezone_set('UTC');

    $date = date('H:i:s', $time / 1000);

    return $date . '.' . $time % 1000;
}

/**
 * Add seconds to the instance. Positive $value travels forward while
 * negative $value travels into the past.
 *
 * @param int $value
 *
 * @return string
 */
function datatime_add_seconds($value)
{
    return Carbon::now()->addSeconds($value)->toDateTimeString();
}

/**
 * 获取毫秒文件名
 *  20191107190401123456
 *
 * @return string
 */
function filename_microtime()
{
    list($usec, $sec) = explode(' ', microtime());

    return date('YmdHms', $sec) . ($usec * 1000000);
}


// +----------------------------------------------------------------------
// | 跨域/浏览器
// +----------------------------------------------------------------------
/**
 * 获取顶级域名
 */
function domain_top(string $host = null)
{
    if (!$host)
    {
        $host = Request::host();
    }
    //防止host中，带有端口
    $host = parse_url($host, PHP_URL_HOST) ?? $host;

    $rtn = preg_match('/[\w][\w-]*\.(?:com\.cn|com|cn|co|net|org|gov|cc|biz|info)(\/|$)/isU',
        $host, $domain);

    return $rtn ? $domain[0] : $rtn;
}

/**
 * 是否是 IE8 浏览器
 */
function is_ie()
{
    $browserName = Browser::getName();

    //IE 浏览器或Flash上传
    if (($browserName == \Sinergi\BrowserDetector\Browser::IE) || strpos(Request::server('HTTP_USER_AGENT') ?? '', 'Flash'))
    {
        return true;
    }

    return false;
}

/**
 * 是否是 Safari 浏览器
 */
function is_safari()
{
    $browserName = Browser::getName();

    //safari 浏览器不支持跨域读写 cookie
    if ($browserName == \Sinergi\BrowserDetector\Browser::SAFARI)
    {
        return true;
    }

    return false;
}

/**
 * 跨域资源共享上传POST - 涉及安全性问题
 */
function crossdomain_cors($methods = 'POST,OPTIONS')
{
    // A wildcard '*' cannot be used in the 'Access-Control-Allow-Origin' header when the credentials flag is true.
    // Origin 'http://localhost:8000' is therefore not allowed access.
    header('Access-Control-Allow-Origin:' . Request::header('origin'));

    // 是否支持cookie跨域
    header('Access-Control-Allow-Credentials:true');

    // 响应类型（所有：GET POST等）
    header('Access-Control-Allow-Methods:' . $methods);

    // 响应头设置（仅仅允许Content-Type）
    header('Access-Control-Allow-Headers: Content-Type,X-Requested-With,X_Requested_With');

    // timeout - 60秒
    header('Keep-Alive:timeout=5, max=60');

    //在这个时间范围内，所有同类型的请求都将不再发送预检请求而是直接使用此次返回的头作为判断依据
    header("Access-Control-Max-Age:1440");
}

/*
 * Os Name to Number
 */
function osname_to_num(string $osName = 'unknown')
{
    switch ($osName)
    {
        case \Sinergi\BrowserDetector\Os::WINDOWS:
            return 1;
        case \Sinergi\BrowserDetector\Os::OSX:
            return 2;
        case \Sinergi\BrowserDetector\Os::LINUX:
            return 3;
        case \Sinergi\BrowserDetector\Os::IOS:
            return 4;
        case \Sinergi\BrowserDetector\Os::ANDROID:
            return 5;
        case \Sinergi\BrowserDetector\Os::WINDOWS_PHONE:
            return 6;
        case \Sinergi\BrowserDetector\Os::FREEBSD:
            return 7;
        case \Sinergi\BrowserDetector\Os::OPENBSD:
            return 8;
        case \Sinergi\BrowserDetector\Os::NOKIA:
            return 9;
        case \Sinergi\BrowserDetector\Os::BLACKBERRY:
            return 10;
        case \Sinergi\BrowserDetector\Os::OS2:
            return 11;
        case \Sinergi\BrowserDetector\Os::SUNOS:
            return 12;
        case \Sinergi\BrowserDetector\Os::CHROME_OS:
            return 13;
        case \Sinergi\BrowserDetector\Os::OPENSOLARIS:
            return 14;
        case \Sinergi\BrowserDetector\Os::SYMBOS:
            return 15;
        case \Sinergi\BrowserDetector\Os::NETBSD:
            return 16;
        case \Sinergi\BrowserDetector\Os::BEOS:
            return 17;
        default:
            return 0;
    }
}


// +----------------------------------------------------------------------
// | session or cookie
// +----------------------------------------------------------------------
function cookie_clear()
{
    $names = Cookie::get();
    foreach ($names as $name => $val)
    {
        Cookie::delete($name);
    }
}


// +----------------------------------------------------------------------
// | 文件/目录
// +----------------------------------------------------------------------
/**
 * 遍历获取某路径下的文件,包括子文件夹
 *
 * @param string $dir 目录名
 *
 * @return array|null 包含完整文件路径级文件名的数组
 */
function get_files_list($dir)
{
    //如果本身就是个文件,直接返回
    if (is_file($dir))
    {
        return array($dir);
    }

    //创建数组,存储文件名
    $files = array();

    if (is_dir($dir) && ($dir_p = opendir($dir))) //路径合法且能访问//创建目录句柄
    {
        $ds = '/';  //目录分隔符
        while (($filename = readdir($dir_p)) !== false)  //返回打开目录句柄中的一个条目
        {
            //排除干扰项
            if ($filename == '.' || $filename == '..')
            {
                continue;
            }

            //获取本条目的类型(文件或文件夹)
            $filetype = filetype($dir . $ds . $filename);

            //如果收文件夹,
            if ($filetype == 'dir')
            {
                //进行递归,并将结果合并到数组中
                $files = array_merge($files, get_files_list($dir . $ds . $filename));
            }
            //如果是文件,
            else if ($filetype == 'file')
            {
                //将文件名转成utf-8后存到数组
                $files[] = mb_convert_encoding($dir . $ds . $filename, 'UTF-8', 'GBK');
            }
        }

        //关闭目录句柄
        closedir($dir_p);
    }
    else //非法路径
    {
        $files = null;
    }

    return $files;
}

/**
 * 从文件中读取内容， 不能超过2M
 *
 * @param string $file   文件
 * @param int    $offset 起始位置 默认，开头
 * @param int    $length 长度 不能超过2M， 默认2M
 *
 * @return bool|string
 */
function get_from_file($file, $offset = 0, $length = 0)
{
    if (!is_file($file))
    {
        return false;
    }

    //打开文件
    if (!$f = fopen($file, 'rb'))
    {
        return false;
    }

    //指针偏移
    if ($offset < 0)
    {
        fseek($f, $offset, SEEK_END);
    }
    else
    {
        fseek($f, $offset);
    }

    //长度

    if ($length > 1024 * 1024 * 2)
    {
        return false;
    }
    else if ($length == 0)
    {
        $length = 1024 * 1024 * 2;
    }

    return @fread($f, $length);
}

/*
 * 根据 text 文本内容，获取二维码
 *
 * 返回 二维码文件的路径
 */
function get_qrfile($text)
{
    $fileName = md5($text);
    if (!is_dir(DIR_IMGS_QRCODEDS))
        mk_dir(DIR_IMGS_QRCODEDS);
    $filePath = DIR_IMGS_QRCODEDS . $fileName;

    if (!is_file($filePath))
    {
        QRcode::png($text, $filePath, QR_ECLEVEL_L, 4, 0); //生成二维码
    }

    return $filePath;
}

/**
 * 小文件下载
 *
 * @param string      $file
 * @param string|null $downloadFileName
 *
 * @return
 */
function download_file(string $file, string $downloadFileName = null, int $expire = 180)
{
    if (is_file($file))
    {
        $fileSize = filesize($file);

        header('Pragma: public');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $fileSize);
        header('Accept-Ranges: bytes');
        header('Accept-Length:' . $fileSize);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');
        header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $expire) . ' GMT');
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0' . ' ,max-age=' . $expire);

        //客户端的弹出对话框，对应的文件名
        if (!isset($downloadFileName))
        {
            $pathInfo         = pathinfo($file);
            $ext              = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : "";
            $downloadFileName = $pathInfo['basename'] . $ext;
        }

        $browserName = Browser::getName();

        if ($browserName == \Sinergi\BrowserDetector\Browser::IE)
        {
            $encoded_filename = rawurlencode($downloadFileName);
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"', false);
        }
        else if ($browserName == \Sinergi\BrowserDetector\Browser::FIREFOX)
        {
            header("Content-Disposition: attachment; filename*=\"utf8''" . $downloadFileName . '"', false);
        }
        else
        {
            header('Content-Disposition: attachment; filename="' . $downloadFileName . '"', false);
        }

        //tp6 模式中，通过返回内容的形式进行 return 操作
        //flush();
        //readfile($file);
        return file_get_contents($file);
    }
}

function rmBOM(string $string)
{
    if (substr($string, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf))
    {
        $string = substr($string, 3);
    }

    return $string;
}

/**
 * 递归地创建目录
 *
 * @param string $pathname 路径
 * @param int    $mode     数字 1 表示使文件可执行，数字 2 表示使文件可写，数字 4 表示使文件可读。相加即$mode
 *
 * @return bool
 */
function mk_dir($pathname, $mode = 0777)
{
    if (is_dir($pathname))
    {
        return true;
    }

    return mkdir($pathname, $mode, true);
}

/**
 * 生成上传文件的的Sub路径 年-月-日-时
 */
function dir_sub_date($sUploadRootDir = null, $bSeparator = true)
{
    $arrays = getdate();
    $year   = $arrays['year'];
    $mon    = $arrays['mon'];
    $day    = $arrays['mday'];
    $hours  = $arrays['hours'];

    $dir = empty($sUploadRootDir) ? $year : $sUploadRootDir . $year;
    if (!is_dir($dir))
    {
        mkdir($dir, 0777, true);
        chmod($dir, 0777);
    }

    $dir .= '/' . $mon;
    if (!is_dir($dir))
    {
        mkdir($dir, 0777, true);
        chmod($dir, 0777);
    }

    $dir .= '/' . $day;
    if (!is_dir($dir))
    {
        mkdir($dir, 0777, true);
        chmod($dir, 0777);
    }

    $dir .= '/' . $hours;
    if (!is_dir($dir))
    {
        mkdir($dir, 0777, true);
        chmod($dir, 0777);
    }

    return $bSeparator ? $dir . DIRECTORY_SEPARATOR : $dir;
}

/**
 * 删除文件夹及其内部文件
 *
 * @param $dir
 *
 * @return bool
 */
function dir_del($dir)
{
    if (!is_dir($dir))
    {
        return false;
    }

    //先删除目录下的文件：
    $dh = opendir($dir);
    while ($file = readdir($dh))
    {
        if ($file != '.' && $file != '..')
        {
            $fullpath = $dir . '/' . $file;
            if (!is_dir($fullpath) && is_writable($fullpath))
            {
                unlink($fullpath);
            }
            else
            {
                dir_del($fullpath);
            }
        }
    }
    closedir($dh);

    //删除当前文件夹：
    if (is_writable($dir))
    {
        rmdir($dir);
    }
    else
    {
        return false;
    }

    return true;
}

/**
 * 导出订单
 *
 * @param array  $headings
 * @param array  $rows
 * @param string $file_name
 *
 * @return bool|string
 */
function export_csv($headings = [], $rows = [], $file_name = '')
{
    if ((!empty($headings)) && (!empty($rows)))
    {
        $file_name = $file_name !== '' ? $file_name : 'export';
        $name      = $file_name . '_' . date('YmdHis') . '.csv'; //构造文件名
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $name);
        $output = fopen("../public/" . $name, 'w');
        array_unshift($rows, $headings); //写入表头
        foreach ($rows as $row)
        { //逐行写入
            $converted = array_map(function ($item) {
                return iconv('UTF-8', 'GBK', $item);
            }, $row);
            fputcsv($output, $converted);
        }

        return IMG_URL . '/' . $name;
    }

    return false;
}

// +----------------------------------------------------------------------
// | 数组
// +----------------------------------------------------------------------
// 不区分大小写的in_array实现
function in_array_case($value, $array)
{
    return in_array(strtolower($value), array_map('strtolower', $array), true);
}

// 对象转数组
function object2array($object)
{
    $object = json_decode(json_encode($object), true);

    return $object;
}

/**
 * 返回数组的维度
 *
 * @param  [type] $arr [description]
 *
 * @return [type]      [description]
 */
function array_level($arr)
{
    $al = array(0);
    function aL($arr, &$al, $level = 0)
    {
        if (is_array($arr))
        {
            $level++;
            $al[] = $level;
            foreach ($arr as $v)
            {
                aL($v, $al, $level);
            }
        }
    }

    aL($arr, $al);

    return max($al);
}

/**
 * 将$table2的内容赋给$table1
 *
 * @param array  $table1
 *                     eg: array(
 *                     ['uid'=>1,name=>'tom'],
 *                     ['uid'=>2,name=>'lily'],
 *                     )
 *
 * @param string $key1 eg:'uid'
 * @param array  $table2
 *                     eg: array(
 *                     ['userid'=>1,age=>12],
 *                     ['userid'=>2,age=>13],
 *                     )
 * @param string $key2 eg:'userid'
 *
 * @return array|bool
 *                     eg: array(
 *                     ['uid'=>1,name=>'tom',age=>12],
 *                     ['uid'=>2,name=>'lily',age=>13],
 *                     )
 */
function array_merge2d($table1, $key1, $table2, $key2)
{
    //获取$table2的所有列
    $colNames2 = [];
    foreach ($table2 as $row2)
    {
        $colNames2 = array_keys($row2);
        break;
    }

    //去除配对的列
    $k = array_search($key2, $colNames2);
    if ($k === false)
    {
        return false;
    }
    unset($colNames2[$k]);

    //用表2填充表1
    foreach ($table1 as $rowNum1 => &$row1)
    {
        foreach ($table2 as $rowNum2 => &$row2)
        {//逐行在表2中匹配

            if ($row1[$key1] == $row2[$key2])
            { //匹配成功

                //逐列将表2 $colName2行的内容赋给表1的$rowNum1行
                foreach ($colNames2 as $colName2)
                {
                    $row1[$colName2] = $row2[$colName2];
                }

                //删掉表2的$colName2行,提高效率
                unset($table2[$rowNum2]);
                break;
            }

        }

    }

    return $table1;
}

/**
 * 将编号列表转为数组
 *
 * @param string $ids 编号列表
 * @param string $sep 分隔符
 *
 * @return array
 */
function ids2array(string $ids = null, string $sep = '|')
{
    if (!isset($ids) || $ids === '')
    {
        return [];
    }

    $arr = explode($sep, trim(trim($ids), $sep));
    if (empty($arr) || reset($arr) === '')
    {
        return [];
    }

    return $arr;
}

/**
 * 笛卡尔积
 *
 *         eg: [[1,2],[3,4]] --> [[1,3],[1,4],[2,3],[2,4]]
 *
 * @param array $arr 将数组的各项值做笛卡尔积
 *
 * @return array
 */
function cartesian_product(array $arr)
{
    //取出$arr的第一项
    $result = array_shift($arr);
    if (!$result)
    {
        return [];
    }

    //依次取出$arr的之后各项
    while ($arr2 = array_shift($arr))
    {
        $arr1   = $result;
        $result = array();
        foreach ($arr1 as $v)
        {
            foreach ($arr2 as $v2)
            {
                if (!is_array($v))
                {
                    $v = array($v);
                }
                if (!is_array($v2))
                {
                    $v2 = array($v2);
                }
                $result[] = array_merge_recursive($v, $v2);
            }
        }
    }

    return $result ?? [];
}

/**
 * 按照值删除数组元素（保持原索引）
 *
 * @param      $array
 * @param      $val
 * @param bool $strict
 *
 * @return bool
 */
function array_del_by_val(&$array, $val, $strict = false)
{
    if (!isset($val) || !is_array($array))
    {
        return false;
    }

    $keys = array_keys($array, $val, $strict);
    foreach ($keys as $key)
    {
        unset($array[$key]);
    }

    return true;
}

/**
 * 删除数组中的某个键（支持多维数组）
 *
 * @param array  $array  数组
 * @param string $delKey 键
 *
 * @return void
 */
function array_del_key(array &$array, string $delKey)
{
    foreach ($array as $key => &$value)
    {
        if ($key === $delKey)
        {
            unset($array[$key]);
        }
        if (is_array($value))
        {
            array_del_key($value, $delKey);
        }
    }
}


// +----------------------------------------------------------------------
// | SQL
// +----------------------------------------------------------------------
/**
 * 构造 区间查询条件
 *
 * @param array      $map       查询条件数组
 * @param string     $filedName 字段名
 * @param string|int $min       最小值
 * @param string|int $max       最大值
 *
 * @return void
 */
function sql_map_region(array &$map, string $filedName, $min, $max)
{
    if (isset($min) && !isset($max))
    {
        $map[] = [$filedName, '>=', $min];
    }
    else if (!isset($min) && isset($max))
    {
        $map[] = [$filedName, '<=', $max];
    }
    else if (isset($min) && isset($max))
    {
        $map[] = [$filedName, 'between', [$min, $max]];
    }
}


// +----------------------------------------------------------------------
// | 表单验证
// +----------------------------------------------------------------------
/**
 * 检查用户名
 *
 * @param $name
 */
function validate_username($name)
{
    // 4-20字符，数字、字母、下划线，以字母开头
    if (!preg_match('/^([a-zA-Z]{1}[_a-zA-Z0-9]{3,19})$/', $name))
    {
        return false;
    }

    return true;
}

/**
 * 检查邮箱
 *
 * @param $email
 */
function validate_email($email)
{
    //检查邮箱
    if (!preg_match('/^[_a-zA-Z0-9\-]+(\.[_a-zA-Z0-9\-]*)*@[a-zA-Z0-9\-]+([\.][a-zA-Z0-9\-]+)+$/', $email) || mb_strlen($email) > 32)
    {
        return false;
    }

    $emailParts = explode('@', $email);

    //“@”前不能有“.”
    if (strpos($emailParts[0], '.') !== false)
    {
        return false;
    }

    $domain = explode('.', $emailParts[1]);
    $str    = end($domain);
    if (preg_match('/\d+/', $str))
    {
        return false;
    }

    return true;
}

/**
 * 检查电话
 *
 * @param $tel
 */
function validate_phone($tel)
{
    if (!(preg_match('/^1[3,4,5,7,8,9]{1}[0-9]{9}$/', $tel)))
    {
        return false;
    }

    return true;
}

/**
 * 检查电话
 *
 * @param $tel
 */
function validate_telphone($tel)
{
    if (!(preg_match('/^1[3,4,5,7,8,9]{1}[0-9]{9}$/', $tel) || preg_match('/^(0[1-9]{2,3}-?)?[0-9]{7,8}$/', $tel)))
    {
        return false;
    }

    return true;
}


// +----------------------------------------------------------------------
// | Third Party
// +----------------------------------------------------------------------
/**
 * 获取微信操作对象
 *
 * 可以理解为单例-工厂模式
 *
 * @param string $type
 *
 * @return Wechat.$type
 */
function & load_wechat($type = '')
{
    static $wechat = array();
    $index = md5(strtolower($type));

    if (!isset($wechat[$index]))
    {
        //公众号配置文件
        $options              = yaconf('openlogin.weixin');
        $options['cachepath'] = runtime_path() . 'log/' . 'data/';

        $wechat[$index] = Loader::get($type, $options);
    }

    return $wechat[$index];
}

/**
 * 发送短信
 *
 * @param string $type   模版类型
 * @param string $mobile 手机号
 * @param array  $code   模版参数值列表
 *
 * @return object
 *  Recommend
 *  Message
 *  RequestId
 *  HostId
 *  Code
 */
function send_sms(string $type = SMS_USER_REGIETER, string $mobile = '00000000000', array $code = ['1234'])
{
    $config = yaconf('aliyun_sms');

    $data = [
        'accessKeyId'     => $config['access_key'],
        'accessKeySecret' => $config['access_secret'],
    ];
    $data = array_merge($data, $config[$type]);

    // 模版中数组内容赋值
    foreach ($data['templateParam'] as $key => &$value)
    {
        $value = array_shift($code);
    }

    $sms = new \app\common\third\AliSms($data);

    return $sms->request($mobile, false);
}

/**
 * 发送邮件
 *
 * @param string $toMail
 * @param string $subject
 * @param string $body
 * @param string $contentType
 * @param bool   $isEncrypt
 *
 * @return bool
 */
function send_email($smtp, $toMail, $subject, $body, $contentType = 'text/html', $isEncrypt = false)
{
    if (is_null($smtp))
    {
        return false;
    }

    if ($isEncrypt)
    {
        $smtp['pwd'] = \PhpCrypt::PHP_Encrypt($smtp['pwd']);
    }

    if ($smtp['security'] == 'null')
        $smtp['security'] = null;

    //用于服务器具有无效证书的情况
    $https['ssl']['verify_peer']      = false;
    $https['ssl']['verify_peer_name'] = false;

    $transport = new \Swift_SmtpTransport($smtp['host'], $smtp['port'], $smtp['security']);
    $transport->setUsername($smtp['user_name']);
    $transport->setPassword($smtp['pwd']);
    $transport->setStreamOptions($https);
    //修改30为10
    $transport->setTimeout(10);

    try
    {
        $message = new \Swift_Message();
        $message->setFrom(array($smtp['from_mail'] => $smtp['from_user']));
        $message->setTo(array($toMail));

        $message->setSubject($subject);
        $message->setBody($body, $contentType, $smtp['charset']);

        $mailer = new \Swift_Mailer($transport);
        $mailer->send($message);
    }
    catch (\Throwable $e)
    {
        E(\EC::MAIL_SEND_ERROR, '邮件发送失败' . $e->getMessage());
    }

    return true;
}

/**
 * 图片上传到图片服务器
 *
 * @param string $file  文件全路径
 * @param array  $param 参数
 *
 * @return array ['code', 'msg', 'result']
 */
function img_upload(string $file, array $param = [])
{
    $sImgConfig = yaconf('s_img');

    $imgService = new \app\common\third\CloudImg($sImgConfig);

    return $imgService->Upload($param, $file, false);
}

/**
 * 图片服务器中的图像执行裁剪操作
 *
 * @param string $file  文件全路径
 * @param array  $param 参数
 * @param string $rPath 返回可访问的地址
 *
 * @return array ['code', 'msg']
 */
function img_mogr(string $url, string $crop)
{
    $sImgConfig = yaconf('s_img');

    $param      = [
        'url'  => $url,
        'crop' => $crop,
    ];
    $imgService = new \app\common\third\CloudImg($sImgConfig);

    return $imgService->Mogr($param, false);
}

/**
 * 获取 IP 信息
 *
 * @return array|mixed
 */
function get_ip_info()
{
    $ip     = Request::ip();
    $ipInfo = Cache::get('ip_info' . $ip);
    if (!$ipInfo)
    {
        $ipInfo = \app\common\third\MapService::GetIpInfo($ip);
        if ($ipInfo)
        {
            Cache::set('ip_info' . $ip, $ipInfo, CACHE_TIME_SQL_DAY);
        }
    }

    return $ipInfo;
}