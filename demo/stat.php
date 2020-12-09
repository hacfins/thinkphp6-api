<?php
/**
 * 程序名称: PHP探针
 * 程序功能: 探测系统的Web服务器运行环境
 * 项目主页: https://github.com/fbcha/phpprobe
 */
error_reporting(0);
$title   = "PHPProbe探针 ";
$name    = "PHPProbe探针 ";
$downUrl = "https://github.com/fbcha/phpprobe";
$version = "v1.4.1";

$is_constantly = true; // 是否开启实时信息, false - 关闭, true - 开启

date_default_timezone_set("Asia/Shanghai");

$getServerHosts     = get_current_user() . '/' . filter_input(INPUT_SERVER, 'SERVER_NAME') . '(' . gethostbyname(filter_input(INPUT_SERVER, 'SERVER_NAME')) . ')'; // 获取服务器域名/ip
$getServerOS        = PHP_OS . ' ' . php_uname('r'); // 获取服务器操作系统
$getServerSoftWare  = filter_input(INPUT_SERVER, 'SERVER_SOFTWARE'); // 获取服务器类型和版本
$getServerLang      = getenv("HTTP_ACCEPT_LANGUAGE"); // 获取服务器语言
$getServerPort      = filter_input(INPUT_SERVER, 'SERVER_PORT'); // 获取服务器端口
$getServerHostName  = php_uname('n'); // 获取服务器主机名
$getServerAdminMail = filter_input(INPUT_SERVER, 'SERVER_ADMIN'); // 获取服务器管理员邮箱
$getServerTzPath    = __FILE__; // 获取探针路径

// 判断操作系统平台
switch (PHP_OS)
{
    case "Linux":
        $svrShow = (false !== $is_constantly) ? ((false !== ($svrInfo = svr_linux())) ? "show" : "none") : "none";
        $svrInfo = array_merge($svrInfo, linux_Network());
        break;
    case "FreeBSD":
        $svrShow = (false !== $is_constantly) ? ((false !== ($svrInfo = svr_freebsd())) ? "show" : "none") : "none";
        $svrInfo = array_merge($svrInfo, freebsd_Network());
        break;
    case "Darwin":
        $svrShow = (false !== $is_constantly) ? ((false !== ($svrInfo = svr_darwin())) ? "show" : "none") : "none";
        $svrInfo = array_merge($svrInfo, darwin_Network());
        break;
    case "WINNT":
        $is_constantly = false;
        $svrShow       = (false !== $is_constantly) ? ((false !== ($svrInfo = svr_winnt())) ? "show" : "none") : "none";
        break;
    default :
        break;
}

function getCpuInfo()
{
    $cpu  = [];
    $str  = file_get_contents("/proc/stat");
    $mode = "/(cpu)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)/";
    preg_match_all($mode, $str, $cpu);
    $total = $cpu[2][0] + $cpu[3][0] + $cpu[4][0] + $cpu[5][0] + $cpu[6][0] + $cpu[7][0] + $cpu[8][0] + $cpu[9][0];
    $time  = $cpu[2][0] + $cpu[3][0] + $cpu[4][0] + $cpu[6][0] + $cpu[7][0] + $cpu[8][0] + $cpu[9][0];

    return [
        'total' => $total,
        'time'  => $time
    ];
}

// linux
function svr_linux()
{
    // 获取CPU信息
    if (false === ($str = file_get_contents("/proc/cpuinfo")))
        return false;

    if (is_array($str))
        $str = implode(":", $str);
    preg_match_all("/model\sname\s+\:(.*)[\r\n]+/isU", $str, $model);
    preg_match_all("/cache\ssize\s+:\s*(.*)[\r\n]+/isU", $str, $cache);
    preg_match_all("/cpu\sMHz\s+:\s*(.*)[\r\n]+/isU", $str, $mhz);
    preg_match_all("/bogomips\s+:\s*(.*)[\r\n]+/isU", $str, $bogomips);
    preg_match_all("/core\sid\s+:\s*(.[1-9])[\r\n]+/isU", $str, $cores);

    if (false !== is_array($model[1]))
    {
        $res['cpu']['core']      = sizeof($cores[1]);
        $res['cpu']['processor'] = sizeof($model[1]);
        $res['cpu']['cores']     = $res['cpu']['core'] . '核' . (($res['cpu']['processor']) ? '/' . $res['cpu']['processor'] . '线程' : '');
        foreach ($model[1] as $k => $v)
        {
            $mhz[1][$k]            = ' | 频率:' . $mhz[1][$k];
            $cache[1][$k]          = ' | 二级缓存:' . $cache[1][$k];
            $bogomips[1][$k]       = ' | Bogomips:' . $bogomips[1][$k];
            $res['cpu']['model'][] = $model[1][$k] . $mhz[1][$k] . $cache[1][$k] . $bogomips[1][$k];
        }

        if (false !== is_array($res['cpu']['model']))
            $res['cpu']['model'] = implode("<br />", $res['cpu']['model']);
    }

    // 获取服务器运行时间
    if (false === ($str = file_get_contents("/proc/uptime")))
        return false;

    $str   = explode(" ", $str);
    $str   = trim($str[0]);
    $min   = $str / 60;
    $hours = $min / 60;
    $days  = floor($hours / 24);
    $hours = floor($hours - ($days * 24));
    $min   = floor($min - ($days * 60 * 24) - ($hours * 60));
    if ($days !== 0)
        $res['uptime'] = $days . "天";
    if ($hours !== 0)
        $res['uptime'] .= $hours . "小时";
    $res['uptime'] .= $min . "分钟";

    // 获取内存信息
    if (false === ($str = file_get_contents("/proc/meminfo")))
        return false;

    preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $mems);
    preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

    $mtotal    = $mems[1][0] * 1024;
    $mfree     = $mems[2][0] * 1024;
    $mbuffers  = $buffers[1][0] * 1024;
    $mcached   = $mems[3][0] * 1024;
    $stotal    = $mems[4][0] * 1024;
    $sfree     = $mems[5][0] * 1024;
    $mused     = $mtotal - $mfree;
    $sused     = $stotal - $sfree;
    $mrealused = $mtotal - $mfree - $mcached - $mbuffers; //真实内存使用

    $res['mTotal']         = size_format($mtotal, 1);
    $res['mFree']          = size_format($mfree, 1);
    $res['mBuffers']       = size_format($mbuffers, 1);
    $res['mCached']        = size_format($mcached, 1);
    $res['mUsed']          = size_format($mtotal - $mfree, 1);
    $res['mPercent']       = (floatval($mtotal) != 0) ? round($mused / $mtotal * 100, 1) : 0;
    $res['mRealUsed']      = size_format($mrealused, 1);
    $res['mRealFree']      = size_format($mtotal - $mrealused, 1); //真实空闲
    $res['mRealPercent']   = (floatval($mtotal) != 0) ? round($mrealused / $mtotal * 100, 1) : 0; //真实内存使用率
    $res['mCachedPercent'] = (floatval($mcached) != 0) ? round($mcached / $mtotal * 100, 1) : 0; //Cached内存使用率
    $res['swapTotal']      = size_format($stotal, 1);
    $res['swapFree']       = size_format($sfree, 1);
    $res['swapUsed']       = size_format($sused, 1);
    $res['swapPercent']    = (floatval($stotal) != 0) ? round($sused / $stotal * 100, 1) : 0;

    $res['mBool'] = true;
    $res['cBool'] = true;
    $res['rBool'] = true;
    $res['sBool'] = true;

    // cpu状态
    if (false === ($str = file_get_contents("/proc/stat")))
        return false;
    $cpuinfo1 = getCpuInfo($str);
    sleep(1);
    $cpuinfo2 = getCpuInfo($str);
    $time     = $cpuinfo2['time'] - $cpuinfo1['time'];
    $total    = $cpuinfo2['total'] - $cpuinfo1['total'];

    $percent               = round($time / $total, 4);
    $percent               = $percent * 100;
    $res['cpu']['percent'] = $percent;

    return $res;
}

// freebsd
function svr_freebsd()
{
    // 获取cpu信息
    if (false === ($res['cpu']['core'] = getCommand("kern.smp.cpus")))
        return false;

    $res['cpu']['cores'] = $res['cpu']['core'] . '核';
    $model               = getCommand("hw.model");
    $res['cpu']['model'] = $model;

    // 获取服务器运行时间
    $uptime = getCommand("kern.boottime");
    $uptime = preg_split("/ /", $uptime);
    $uptime = preg_replace('/,/', '', $uptime[3]);

    $str   = time() - $uptime;
    $min   = $str / 60;
    $hours = $min / 60;
    $days  = floor($hours / 24);
    $hours = floor($hours - ($days * 24));
    $min   = floor($min - ($days * 60 * 24) - ($hours * 60));
    if ($days !== 0)
        $res['uptime'] = $days . "天";
    if ($hours !== 0)
        $res['uptime'] .= $hours . "小时";
    $res['uptime'] .= $min . "分钟";

    // 获取内存信息
    if (false === ($mTatol = getCommand("hw.physmem")))
        return false;
    $pagesize = getCommand("hw.pagesize");
    $vmstat   = getCommand("", "vmstat", "");
    $cached   = getCommand("vm.stats.vm.v_cache_count");
    $active   = getCommand("vm.stats.vm.v_active_count");
    $wire     = getCommand("vm.stats.vm.v_wire_count");
    $swapstat = getCommand("", "swapctl", "-l -k");

    $mlines = preg_split("/\n/", $vmstat, -1, 1);
    $mbuf   = preg_split("/\s+/", trim($mlines[2]), 19);
    $slines = preg_split("/\n/", $swapstat, -1, 1);
    $sbuf   = preg_split("/\s+/", $slines[1], 6);

    $app      = ($active + $wire) * $pagesize;
    $mTatol   = $mTatol;
    $mFree    = $mbuf[4] * 1024;
    $mCached  = $cached * $pagesize;
    $mUsed    = $mTatol - $mFree;
    $mBuffers = $mUsed - $app - $mCached;
    $sTotal   = $sbuf[1] * 1024;
    $sUsed    = $sbuf[2] * 1024;
    $sFree    = $sTotal - $sUsed;

    $res['mTotal']         = size_format($mTatol, 1);
    $res['mFree']          = size_format($mFree, 1);
    $res['mCached']        = size_format($mCached, 1);
    $res['mUsed']          = size_format($mUsed, 1);
    $res['mBuffers']       = size_format($mBuffers, 1);
    $res['mPercent']       = (floatval($mTatol) != 0) ? round($mUsed / $mTatol * 100, 1) : 0;
    $res['mCachedPercent'] = (floatval($mCached) != 0) ? round($mCached / $mTatol * 100, 1) : 0; //Cached内存使用率
    $res['swapTotal']      = size_format($sTotal, 1);
    $res['swapFree']       = size_format($sFree, 1);
    $res['swapUsed']       = size_format($sUsed, 1);
    $res['swapPercent']    = (floatval($sTotal) != 0) ? round($sUsed / $sTotal * 100, 1) : 0;

    $res['mBool'] = true;
    $res['cBool'] = true;
    $res['rBool'] = false;
    $res['sBool'] = true;

    // CPU状态
    $cpustat               = $mbuf;
    $percent               = $cpustat[16] + $cpustat[17];
    $res['cpu']['percent'] = $percent;

    return $res;
}

// Darwin
function svr_darwin()
{
    // 获取CPU信息
    if (false === ($res['cpu']['core'] = getCommand("machdep.cpu.core_count")))
        return false;

    $res['cpu']['processor'] = getCommand("machdep.cpu.thread_count");
    $res['cpu']['cores']     = $res['cpu']['core'] . '核' . (($res['cpu']['processor']) ? '/' . $res['cpu']['processor'] . '线程' : '');
    $model                   = getCommand("machdep.cpu.brand_string");
    $cache                   = getCommand("machdep.cpu.cache.size") * $res['cpu']['core'];
    $cache                   = size_format($cache * 1024, 0);
    $res['cpu']['model']     = $model . ' [二级缓存：' . $cache . ']';

    // 获取服务器运行时间
    $uptime = getCommand("kern.boottime");
    preg_match_all('#(?<={)\s?sec\s?=\s?+\d+#', $uptime, $matches);
    $_uptime = explode('=', $matches[0][0])[1];

    $str   = time() - $_uptime;
    $min   = $str / 60;
    $hours = $min / 60;
    $days  = floor($hours / 24);
    $hours = floor($hours - ($days * 24));
    $min   = floor($min - ($days * 60 * 24) - ($hours * 60));
    if ($days !== 0)
        $res['uptime'] = $days . "天";
    if ($hours !== 0)
        $res['uptime'] .= $hours . "小时";
    $res['uptime'] .= $min . "分钟";

    // 获取内存信息
    if (false === ($mTatol = getCommand("hw.memsize")))
        return false;
    $vmstat = getCommand("", 'vm_stat', '');
    if (preg_match('/^Pages free:\s+(\S+)/m', $vmstat, $mfree))
    {
        if (preg_match('/^File-backed pages:\s+(\S+)/m', $vmstat, $mcache))
        {
            // OS X 10.9 or never
            $mFree   = $mfree[1] * 4 * 1024;
            $mCached = $mcache[1] * 4 * 1024;
            if (preg_match('/^Pages occupied by compressor:\s+(\S+)/m', $vmstat, $mbuffer))
            {
                $mBuffer = $mbuffer[1] * 4 * 1024;
            }
        }
        else
        {
            if (preg_match('/^Pages speculative:\s+(\S+)/m', $vmstat, $spec_buf))
            {
                $mFree = ($mfree[1] + $spec_buf[1]) * 4 * 1024;
            }
            else
            {
                $mFree = $mfree[1] * 4 * 1024;
            }
            if (preg_match('/^Pages inactive:\s+(\S+)/m', $vmstat, $inactive_buf))
            {
                $mCached = $inactive_buf[1] * 4 * 1024;
            }
        }
    }
    else
    {
        return false;
    }

    $mUsed = $mTatol - $mFree;

    $res['mTotal']         = size_format($mTatol, 1);
    $res['mFree']          = size_format($mFree, 1);
    $res['mBuffers']       = size_format($mBuffer, 1);
    $res['mCached']        = size_format($mCached, 1);
    $res['mUsed']          = size_format($mUsed, 1);
    $res['mPercent']       = (floatval($mTatol) != 0) ? round($mUsed / $mTatol * 100, 1) : 0;
    $res['mCachedPercent'] = (floatval($mCached) != 0) ? round($mCached / $mTatol * 100, 1) : 0; //Cached内存使用率

    $swapInfo = getCommand("vm.swapusage");
    $swap1    = preg_split('/M/', $swapInfo);
    $swap2    = preg_split('/=/', $swap1[0]);
    $swap3    = preg_split('/=/', $swap1[1]);
    $swap4    = preg_split('/=/', $swap1[2]);

    $sTotal = $swap2[1] * 1024 * 1024;
    $sUsed  = $swap3[1] * 1024 * 1024;
    $sFree  = $swap4[1] * 1024 * 1024;

    $res['swapTotal']   = size_format($sTotal, 1);
    $res['swapFree']    = size_format($sFree, 1);
    $res['swapUsed']    = size_format($sUsed, 1);
    $res['swapPercent'] = (floatval($sTotal) != 0) ? round($sUsed / $sTotal * 100, 1) : 0;

    $res['mBool'] = true;
    $res['cBool'] = true;
    $res['rBool'] = false;
    $res['sBool'] = true;

    // CPU状态
    $cpustat = getCommand(1, 'sar', '');
    preg_match_all("/Average\s{0,}\:+\s+\w+\s+\w+\s+\w+\s+\w+/s", $cpustat, $_cpu);
    $_cpu                  = preg_split("/\s+/", $_cpu[0][0]);
    $percent               = $_cpu[1] + $_cpu[2] + $_cpu[3];
    $res['cpu']['percent'] = $percent;

    return $res;
}

function getCommand($args = '', $commandName = 'sysctl', $option = '-n')
{
    if (false === ($commandPath = findCommand($commandName)))
        return false;

    if ($command = shell_exec("$commandPath $option $args"))
    {
        return trim($command);
    }

    return false;
}

function findCommand($commandName)
{
    $paths = ['/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin'];

    foreach ($paths as $path)
    {
        if (is_executable("$path/$commandName"))
            return "$path/$commandName";
    }

    return false;
}

// winnt
function svr_winnt()
{
    // 获取CPU信息
    if (get_cfg_var("com.allow_dcom"))
    {
        $wmi     = new COM('winmgmts:{impersonationLevel=impersonate}');
        $cpuinfo = getWMI($wmi, "Win32_Processor", "Name,LoadPercentage,NumberOfCores,NumberOfLogicalProcessors,L2CacheSize");
    }
    else if (function_exists('exec'))
    {
        exec("wmic cpu get LoadPercentage,NumberOfCores,NumberOfLogicalProcessors,L2CacheSize", $cpuwmic);
        exec("wmic cpu get Name", $cpuname);
        $cpuKey   = preg_split("/ +/", $cpuwmic[0]);
        $cpuValue = preg_split("/ +/", $cpuwmic[1]);
        foreach ($cpuKey as $k => $v)
        {
            $cpuinfo[$v] = $cpuValue[$k];
        }
        $cpuinfo['Name'] = $cpuname[1];
    }
    else
    {
        return false;
    }

    $res['cpu']['core']      = $cpuinfo['NumberOfCores'];
    $res['cpu']['processor'] = $cpuinfo['NumberOfLogicalProcessors'];
    $res['cpu']['cores']     = $res['cpu']['core'] . '核' . (($res['cpu']['processor']) ? '/' . $res['cpu']['processor'] . '线程' : '');
    $cache                   = size_format($cpuinfo['L2CacheSize'] * 1024, 0);
    $res['cpu']['model']     = $cpuinfo['Name'] . ' [二级缓存：' . $cache . ']';

    // 获取服务器运行时间
    if (get_cfg_var("com.allow_dcom"))
    {
        $sysinfo = getWMI($wmi, "Win32_OperatingSystem", "LastBootUpTime,TotalVisibleMemorySize,FreePhysicalMemory");
    }
    else if (function_exists("exec"))
    {
        exec("wmic os get LastBootUpTime,TotalVisibleMemorySize,FreePhysicalMemory", $osInfo);
        $osKey   = preg_split("/ +/", $osInfo[0]);
        $osValue = preg_split("/ +/", $osInfo[1]);
        foreach ($osKey as $key => $value)
        {
            $sysinfo[$value] = $osValue[$key];
        }
    }
    else
    {
        return false;
    }

    $res['uptime'] = $sysinfo['LastBootUpTime'];
    $str           = time() - strtotime(substr($res['uptime'], 0, 14));
    $min           = $str / 60;
    $hours         = $min / 60;
    $days          = floor($hours / 24);
    $hours         = floor($hours - ($days * 24));
    $min           = floor($min - ($days * 60 * 24) - ($hours * 60));
    if ($days !== 0)
        $res['uptime'] = $days . "天";
    if ($hours !== 0)
        $res['uptime'] .= $hours . "小时";
    $res['uptime'] .= $min . "分钟";

    // 获取内存信息
    $mTotal = $sysinfo['TotalVisibleMemorySize'] * 1024;
    $mFree  = $sysinfo['FreePhysicalMemory'] * 1024;
    $mUsed  = $mTotal - $mFree;

    $res['mTotal']   = size_format($mTotal, 1);
    $res['mFree']    = size_format($mFree, 1);
    $res['mUsed']    = size_format($mUsed, 1);
    $res['mPercent'] = round($mUsed / $mTotal * 100, 1);

    if (get_cfg_var("com.allow_dcom"))
    {
        $swapinfo = getWMI($wmi, "Win32_PageFileUsage", 'AllocatedBaseSize,CurrentUsage');
    }
    else if (function_exists("exec"))
    {
        exec("wmic pagefile get AllocatedBaseSize,CurrentUsage", $swaps);
        $swapKey   = preg_split("/ +/", $swaps[0]);
        $swapValue = preg_split("/ +/", $swaps[1]);
        foreach ($swapKey as $sk => $sv)
        {
            $swapinfo[$sv] = $swapValue[$sk];
        }
    }
    else
    {
        return false;
    }
    $sTotal = $swapinfo['AllocatedBaseSize'] * 1024 * 1024;
    $sUsed  = $swapinfo['CurrentUsage'] * 1024 * 1024;
    $sFree  = $sTotal - $sUsed;

    $res['swapTotal']   = size_format($sTotal, 1);
    $res['swapUsed']    = size_format($sUsed, 1);
    $res['swapFree']    = size_format($sFree, 1);
    $res['swapPercent'] = (floatval($sTotal) != 0) ? round($sUsed / $sTotal * 100, 1) : 0;

    $res['mBool'] = true;
    $res['cBool'] = false;
    $res['rBool'] = false;
    $res['sBool'] = true;

    // CPU状态
    $res['cpu']['percent'] = $cpuinfo['LoadPercentage'];

    return $res;
}

function getWMI($wmi, $strClass, $strValue)
{
    $object  = $wmi->ExecQuery("SELECT {$strValue} FROM {$strClass}");
    $value   = explode(",", $strValue);
    $arrData = [];
    foreach ($value as $v)
    {
        foreach ($object as $info)
        {
            $arrData[$v] = $info->$v;
        }
    }

    return $arrData;
}

function size_format($bytes, $decimals = 2)
{
    $quant = array(
        'TB' => 1099511627776, // pow( 1024, 4)
        'GB' => 1073741824, // pow( 1024, 3)
        'MB' => 1048576, // pow( 1024, 2)
        'KB' => 1024, // pow( 1024, 1)
        'B ' => 1, // pow( 1024, 0)
    );

    foreach ($quant as $unit => $mag)
    {
        if (doubleval($bytes) >= $mag)
        {
            return number_format($bytes / $mag, $decimals) . ' ' . $unit;
        }
    }

    return false;
}

// 网络流量
// linux
function linux_Network()
{
    $net          = [];
    $netstat      = file_get_contents('/proc/net/dev');
    $res['nBool'] = $netstat ? true : false;
    $bufe         = preg_split("/\n/", $netstat, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($bufe as $buf)
    {
        if (preg_match('/:/', $buf))
        {
            list($dev_name, $stats_list) = preg_split('/:/', $buf, 2);
            $dev_name                  = trim($dev_name);
            $stats                     = preg_split('/\s+/', trim($stats_list));
            $net[$dev_name]['name']    = $dev_name;
            $net[$dev_name]['rxbytes'] = netSize($stats[0]);
            $net[$dev_name]['txbytes'] = netSize($stats[8]);
            $net[$dev_name]['rxspeed'] = $stats[0];
            $net[$dev_name]['txspeed'] = $stats[8];
            $net[$dev_name]['errors']  = $stats[2] + $stats[10];
            $net[$dev_name]['drops']   = $stats[3] + $stats[11];
        }
    }
    $res['net'] = $net;

    return $res;
}

// darwin
function darwin_Network()
{
    $netstat      = getCommand("-nbdi | cut -c1-24,42- | grep Link", "netstat");
    $res['nBool'] = $netstat ? true : false;
    $nets         = preg_split("/\n/", $netstat, -1, PREG_SPLIT_NO_EMPTY);
    $_net         = [];
    foreach ($nets as $net)
    {
        $buf = preg_split("/\s+/", $net, 10);
        if (!empty($buf[0]))
        {
            $dev_name                   = trim($buf[0]);
            $_net[$dev_name]['name']    = $dev_name;
            $_net[$dev_name]['rxbytes'] = netSize($buf[5]);
            $_net[$dev_name]['txbytes'] = netSize($buf[8]);
            $_net[$dev_name]['rxspeed'] = $buf[5];
            $_net[$dev_name]['txspeed'] = $buf[8];
            $_net[$dev_name]['errors']  = $buf[4] + $buf[7];
            $_net[$dev_name]['drops']   = isset($buf[10]) ? $buf[10] : "NULL";
        }
    }
    $res['net'] = $_net;

    return $res;
}

// freebsd
function freebsd_Network()
{
    $netstat      = getCommand("-nibd", "netstat");
    $res['nBool'] = $netstat ? true : false;
    $nets         = preg_split("/\n/", $netstat, -1, PREG_SPLIT_NO_EMPTY);
    $_net         = [];
    foreach ($nets as $net)
    {
        $buf = preg_split("/\s+/", $net);
        if (!empty($buf[0]))
        {
            if (preg_match('/^<Link/i', $buf[2]))
            {
                $dev_name                = trim($buf[0]);
                $_net[$dev_name]['name'] = $dev_name;
                if (strlen($buf[3]) < 17)
                {
                    if (isset($buf[11]) && (trim($buf[11]) != ''))
                    {
                        $_net[$dev_name]['rxbytes'] = netSize($buf[6]);
                        $_net[$dev_name]['txbytes'] = netSize($buf[9]);
                        $_net[$dev_name]['rxspeed'] = $buf[6];
                        $_net[$dev_name]['txspeed'] = $buf[9];
                        $_net[$dev_name]['errors']  = $buf[4] + $buf[8];
                        $_net[$dev_name]['drops']   = $buf[11] + $buf[5];
                    }
                    else
                    {
                        $_net[$dev_name]['rxbytes'] = netSize($buf[5]);
                        $_net[$dev_name]['txbytes'] = netSize($buf[8]);
                        $_net[$dev_name]['rxspeed'] = $buf[5];
                        $_net[$dev_name]['txspeed'] = $buf[8];
                        $_net[$dev_name]['errors']  = $buf[4] + $buf[7];
                        $_net[$dev_name]['drops']   = $buf[10];
                    }
                }
                else
                {
                    if (isset($buf[12]) && (trim($buf[12]) != ''))
                    {
                        $_net[$dev_name]['rxbytes'] = netSize($buf[7]);
                        $_net[$dev_name]['txbytes'] = netSize($buf[10]);
                        $_net[$dev_name]['rxspeed'] = $buf[7];
                        $_net[$dev_name]['txspeed'] = $buf[10];
                        $_net[$dev_name]['errors']  = $buf[5] + $buf[9];
                        $_net[$dev_name]['drops']   = $buf[12] + $buf[6];
                    }
                    else
                    {
                        $_net[$dev_name]['rxbytes'] = netSize($buf[6]);
                        $_net[$dev_name]['txbytes'] = netSize($buf[9]);
                        $_net[$dev_name]['rxspeed'] = $buf[6];
                        $_net[$dev_name]['txspeed'] = $buf[9];
                        $_net[$dev_name]['errors']  = $buf[5] + $buf[8];
                        $_net[$dev_name]['drops']   = $buf[11];
                    }
                }
            }
        }
    }
    $res['net'] = $_net;

    return $res;
}

function netSize($size, $decimals = 2)
{
    if ($size < 1024)
    {
        $unit = "Bbps";
    }
    else if ($size < 10240)
    {
        $size = round($size / 1024, $decimals);
        $unit = "Kbps";
    }
    else if ($size < 102400)
    {
        $size = round($size / 1024, $decimals);
        $unit = "Kbps";
    }
    else if ($size < 1048576)
    {
        $size = round($size / 1024, $decimals);
        $unit = "Kbps";
    }
    else if ($size < 10485760)
    {
        $size = round($size / 1048576, $decimals);
        $unit = "Mbps";
    }
    else if ($size < 104857600)
    {
        $size = round($size / 1048576, $decimals);
        $unit = "Mbps";
    }
    else if ($size < 1073741824)
    {
        $size = round($size / 1048576, $decimals);
        $unit = "Mbps";
    }
    else
    {
        $size = round($size / 1073741824, $decimals);
        $unit = "Gbps";
    }

    $size .= $unit;

    return $size;
}

if ($is_constantly)
{
    $currentTime = date("Y-m-d H:i:s");
    $uptime      = $svrInfo['uptime'];
}

// hdd
$hddTotal   = disk_total_space(".");
$hddFree    = disk_free_space(".");
$hddUsed    = $hddTotal - $hddFree;
$hddPercent = (floatval($hddTotal) != 0) ? round($hddUsed / $hddTotal * 100, 2) : 0;

if (filter_input(INPUT_GET, 'act') == 'rt' && $is_constantly)
{
    $res     = array(
        'currentTime'         => $currentTime,
        'uptime'              => $uptime,
        'cpuPercent'          => $svrInfo['cpu']['percent'],
        'MemoryUsed'          => $svrInfo['mUsed'],
        'MemoryFree'          => $svrInfo['mFree'],
        'MemoryPercent'       => $svrInfo['mPercent'],
        'MemoryCachedPercent' => $svrInfo['mCachedPercent'],
        'MemoryCached'        => $svrInfo['mCached'],
        'MemoryRealUsed'      => $svrInfo['mRealUsed'],
        'MemoryRealFree'      => $svrInfo['mRealFree'],
        'MemoryRealPercent'   => $svrInfo['mRealPercent'],
        'Buffers'             => $svrInfo['mBuffers'],
        'SwapFree'            => $svrInfo['swapFree'],
        'SwapUsed'            => $svrInfo['swapUsed'],
        'SwapPercent'         => $svrInfo['swapPercent']
    );
    $jsonRes = json_encode($res);
    echo filter_input(INPUT_GET, 'callback') . '(' . $jsonRes . ')';
    exit();
}
if (filter_input(INPUT_GET, 'act') == 'ort' && $svrInfo['nBool'])
{
    $oRes   = array(
        'Network' => $svrInfo['net']
    );
    $ortRes = json_encode($oRes);
    echo filter_input(INPUT_GET, 'callback') . '(' . $ortRes . ')';
    exit();
}

?>
<!--
       __                                   __
      /\ \                                 /\ \
 _____\ \ \___   _____   _____   _ __   ___\ \ \____     __
/\ '__`\ \  _ `\/\ '__`\/\ '__`\/\`'__\/ __`\ \ '__`\  /'__`\
\ \ \L\ \ \ \ \ \ \ \L\ \ \ \L\ \ \ \//\ \L\ \ \ \L\ \/\  __/
 \ \ ,__/\ \_\ \_\ \ ,__/\ \ ,__/\ \_\\ \____/\ \_,__/\ \____\
  \ \ \/  \/_/\/_/\ \ \/  \ \ \/  \/_/ \/___/  \/___/  \/____/
   \ \_\           \ \_\   \ \_\
    \/_/            \/_/    \/_/

 _______ .______     ______  __    __       ___
|   ____||   _  \   /      ||  |  |  |     /   \
|  |__   |  |_)  | |  ,----'|  |__|  |    /  ^  \
|   __|  |   _  <  |  |     |   __   |   /  /_\  \
|  |     |  |_)  | |  `----.|  |  |  |  /  _____  \
|__|     |______/   \______||__|  |__| /__/     \__\
-->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title . $version; ?> - by fbcha shooter</title>
    <link href="http://g.alicdn.com/sj/dpl/1.5.1/css/sui.min.css" rel="stylesheet" />
    <script type="text/javascript" src="http://g.alicdn.com/sj/lib/jquery.min.js"></script>
    <script type="text/javascript" src="http://g.alicdn.com/sj/dpl/1.5.1/js/sui.min.js"></script>
    <script type="text/javascript" src="//cdn.bootcss.com/echarts/3.2.3/echarts.min.js"></script>
    <style type="text/css">
        body {
            font-size : 1.25vw;
        }

        form {
            margin  : 0;
            padding : 0;
        }

        .stxt {
            font-size : 1vw;
            color     : #666;
        }

        .ext-tag-font li {
            font-size : 1.1vw;
        }

        .red {
            color : #CC0000;
        }

        td.text-center {
            text-align : center;
        }

        td.p0 {
            padding : 0;
        }

        table.test-table {
            margin  : 0;
            padding : 0;
        }

        .sui-table td.bl0 {
            border-left : 0;
        }

        .sui-table td.test-td {
            padding : 10px 0 5px 0;
        }

        .sui-table td.test-td .add-on {
            font-size   : 1.1vw;
            height      : 22px;
            line-height : 22px;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function () {
             <?php if($svrInfo['nBool']){ ?>
            getNetwork();
            <?php } ?>
            <?php if($svrShow === 'show'){ ?>
            getRealTime();
            getCpuStatus();
            getMemory();
            getHdd();
            <?php }?>
        });
        <?php if($svrInfo['nBool']){ ?>
        var inputSpeed = [], outSpeed = [];
        <?php foreach($svrInfo['net'] as $netkey => $netvar){ ?>
        inputSpeed["<?php echo $netvar['name']; ?>"] = <?php echo $netvar['rxspeed']; ?>;
        outSpeed["<?php echo $netvar['name']; ?>"]   = <?php echo $netvar['txspeed']; ?>;
        <?php } ?>
        function getNetwork() {
            setTimeout("getNetwork()", 1000);
            $.getJSON("?act=ort&callback=?", function (data) {
                var items = data['Network'];
                for (var item in items) {
                    $('#' + items[item].name + '_rxbytes').html(items[item].rxbytes);
                    $('#' + items[item].name + '_txbytes').html(items[item].txbytes);
                    $('#' + items[item].name + '_errors').html(items[item].errors);
                    $('#' + items[item].name + '_drops').html(items[item].drops);
                    $('#' + items[item].name + '_rxspeed').html(ForDight((items[item].rxspeed - inputSpeed[items[item].name]), 3));
                    $('#' + items[item].name + '_txspeed').html(ForDight((items[item].txspeed - outSpeed[items[item].name]), 3));
                    inputSpeed[items[item].name] = items[item].rxspeed;
                    outSpeed[items[item].name]   = items[item].txspeed;
                }
            });
        }

        function ForDight(Dight, How) {
            if (Dight < 0) {
                var Last = 0 + "B/s";
            } else if (Dight < 1024) {
                var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "B/s";
            } else if (Dight < 1048576) {
                Dight    = Dight / 1024;
                var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "K/s";
            } else {
                Dight    = Dight / 1048576;
                var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "M/s";
            }
            return Last;
        }
        <?php } ?>
        <?php if($svrShow === 'show'){ ?>
        function size_format(bytes, decimals = 4) {
            if (bytes === 0) return '0 B';

            var k = 1024;
            sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            i     = Math.floor(Math.log(bytes) / Math.log(k));

            return (bytes / Math.pow(k, i)).toPrecision(decimals) + ' ' + sizes[i];
        }

        function getRealTime() {
            setTimeout("getRealTime()", 1000);
            var $rtArr = [
                'currentTime',
                'uptime',
                'MemoryUsed',
                'MemoryFree',
                'MemoryCached',
                'MemoryRealUsed',
                'MemoryRealFree',
                'Buffers',
                'SwapFree',
                'SwapUsed',
                'Network'
            ];
            $.getJSON("?act=rt&callback=?", function (data) {
                for (var i = 0; i < $rtArr.length; i++) {
                    var item = $rtArr[i];
                    $("#" + item).html(data[item]);
                }
            });
        }

        function getMemory() {
            var myChart = echarts.init(document.getElementById('main'));

            var memory_type = ['物理内存', 'Cache', '真实内存', 'SWAP'];
            var is_memory   = ["<?php echo $svrInfo['mBool']; ?>", "<?php echo $svrInfo['cBool']; ?>", "<?php echo $svrInfo['rBool']; ?>", "<?php echo $svrInfo['sBool']; ?>"];
            var percent     = ['MemoryPercent', 'MemoryCachedPercent', 'MemoryRealPercent', 'SwapPercent'];
            var options     = [];
            var centers     = 15;
            for (var i = 0; i < memory_type.length; i++) {
                if (is_memory[i]) {
                    options[i] = {
                        name     : memory_type[i],
                        type     : 'gauge',
                        radius   : '80%',
                        center   : [centers + '%', '50%'],
                        axisLine : {
                            show     : true,
                            lineStyle: {
                                width: 10
                            }
                        },
                        splitLine: {
                            show  : true,
                            length: '15%'
                        },
                        axisTick : {
                            show  : true,
                            length: '10%'
                        },
                        axisLabel: {
                            show     : true,
                            textStyle: {
                                fontSize: 9
                            }
                        },
                        detail   : {
                            show        : true,
                            formatter   : '{value}%',
                            offsetCenter: ['0', '65%'],
                            textStyle   : {
                                fontSize: '14'
                            }
                        },
                        pointer  : {
                            width: 5
                        },
                        data     : [{value: 50, name: memory_type[i]}]
                    };
                    centers    = centers + 23;
                } else {
                    options[i] = "";
                }
            }

            var option = {
                tooltip: {
                    formatter: "{a} <br/>{b} : {c}%"
                },
                series : options
            };

            timeTicket = setInterval(function () {
                $.getJSON("?act=rt&callback=?", function (data) {
                    for (var i = 0; i < percent.length; i++) {
                        if (data[percent[i]] !== null) option.series[i].data[0].value = data[percent[i]];
                    }
                });
                myChart.setOption(option, true);
            }, 1000);
        }

        // cpu使用率

        function getCpuStatus() {
            var cpuChart = echarts.init(document.getElementById('cpustatus'));

            var data       = [];
            var time       = [];
            var _data      = [];
            var cpuPercent = 0;
            var now        = +new Date();

            function randomData() {
                now  = new Date(+now + 1000);
                time = (now).getTime();
                $.getJSON("?act=rt&callback=?", function (d) {
                    cpuPercent = d['cpuPercent'];
                });
                _data = {
                    name : time,
                    value: [
                        time,
                        cpuPercent
                    ]
                };
                return _data;
            }

            for (var i = 0; i < 60; i++) {
                data.push(randomData());
            }

            var option = {
                title : {
                    show: true,
                    text: 'CPU使用率',
                    left: 'center'
                },
                xAxis : {
                    type     : 'time',
                    splitLine: {
                        show: false
                    }
                },
                yAxis : {
                    type       : 'value',
                    boundaryGap: [0, '100%'],
                    max        : 100,
                    splitLine  : {
                        show: true
                    },
                    axisLabel  : {
                        formatter: '{value} %'
                    }
                },
                series: [{
                    name          : 'CPU使用率',
                    type          : 'line',
                    showSymbol    : false,
                    hoverAnimation: false,
                    data          : data
                }]
            };
            cpuChart.setOption(option);
            timeTicket = setInterval(function () {
                data.shift();
                data.push(randomData());

                cpuChart.setOption({
                    series: [{
                        data: data
                    }]
                });
            }, 1000);
        }

        function getHdd() {
            var hddChart = echarts.init(document.getElementById('hddstatus'));

            var option = {
                title  : {
                    text : '总空间 <?php echo size_format($hddTotal, 3); ?>， 使用率 <?php echo $hddPercent; ?> %',
                    right: '10%'
                },
                tooltip: {
                    trigger  : 'item',
                    formatter: function (data) {
                        var seriesName = data.seriesName;
                        var name       = data.name;
                        var value      = size_format(data.value, 5);
                        var percent    = data.percent;
                        return seriesName + '<br />' + name + ': ' + value + ' (' + percent + ' %)';
                    }
                },
                legend : {
                    orient: 'vertical',
                    left  : 'right',
                    data  : ['已用', '空闲']
                },
                series : [
                    {
                        name     : '硬盘使用状况',
                        type     : 'pie',
                        radius   : '80%',
                        center   : ['30%', '50%'],
                        data     : [
                            {value:<?php echo $hddUsed; ?>, name: '已用'},
                            {value:<?php echo $hddFree; ?>, name: '空闲'}
                        ],
                        itemStyle: {
                            emphasis: {
                                shadowBlur   : 10,
                                shadowOffsetX: 0,
                                shadowColor  : 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            };

            hddChart.setOption(option, true);
        }
        <?php }?>
    </script>
</head>
<body>
<div class="sui-container">
    <div class="sui-content">
        <table class="sui-table table-bordered table-primary">
            <thead>
            <tr>
                <th colspan="4">
                                <span class="">
                                    服务器基本主息
                                </span>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td width="20%">
                    服务器域名/IP地址
                </td>
                <td>
                    <?php echo $getServerHosts; ?>
                </td>
                <td>
                    服务器操作系统
                </td>
                <td>
                    <?php echo $getServerOS; ?>
                </td>
            </tr>
            <tr>
                <td>
                    服务器解译引擎
                </td>
                <td>
                    <?php echo $getServerSoftWare; ?>
                </td>
                <td width="20%">
                    服务器语言
                </td>
                <td>
                    <?php echo $getServerLang; ?>
                </td>
            </tr>
            <tr>
                <td>
                    服务器端口
                </td>
                <td>
                    <?php echo $getServerPort; ?>
                </td>
                <td>
                    服务器主机名
                </td>
                <td>
                    <?php echo $getServerHostName; ?>
                </td>
            </tr>
            <tr>
                <td>
                    管理员邮箱
                </td>
                <td>
                    <?php echo $getServerAdminMail; ?>
                </td>
                <td>
                    探针路径
                </td>
                <td>
                    <?php echo $getServerTzPath; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php if ($svrShow == 'show') { ?>
            <table class="sui-table table-bordered table-primary">
                <thead>
                <tr>
                    <th colspan="4">服务器实时数据</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td width="20%">服务器当前时间</td>
                    <td width="30%"><span id="currentTime"></span></td>
                    <td width="20%">服务器已运行时间</td>
                    <td width="30%"><span id="uptime"></span></td>
                </tr>
                <tr>
                    <td>CPU型号 <span class="red">[<?php echo $svrInfo['cpu']['cores']; ?>]</span></td>
                    <td colspan="3"><?php echo $svrInfo['cpu']['model']; ?></td>
                </tr>
                <tr>
                    <td>CPU使用状况</td>
                    <td colspan="3">
                        <div id="cpustatus" style="width: 100%; height: 300px;"></div>
                    </td>
                </tr>
                <tr>
                    <td>内存使用状况</td>
                    <td colspan="3">
                        <div id="main" style="width: 100%;height:200px;"></div>
                        <div class="stxt">
                            物理内存：共 <span id="MemoryTotal" class="red"><?php echo $svrInfo['mTotal']; ?></span>,
                            已用 <span id="MemoryUsed" class="red"></span>,
                            空闲 <span id="MemoryFree" class="red"></span> -
                            <?php if ($svrInfo['sBool'] > 0) { ?>
                                SWAP区：共 <span id="SwapTotal" class="red"><?php echo $svrInfo['swapTotal']; ?></span> ,
                                                                                                                     已使用
                                <span id="SwapUsed" class="red"></span> ,
                                                                        空闲 <span id="SwapFree" class="red"></span>
                            <?php } ?>
                        </div>

                        <div class="stxt">
                            <?php if ($svrInfo['rBool'] > 0) { ?>
                                真实内存使用 <span id="MemoryRealUsed" class="red"></span> ,
                                                                                     真实内存空闲 <span id="MemoryRealFree"
                                                                                                  class="red"></span> -
                            <?php } ?>
                            <?php if ($svrInfo['cBool'] > 0) { ?>
                                Cache： <span id="MemoryCached" class="red"></span> |
                                                                                   Buffers缓冲为 <span id="Buffers"
                                                                                                    class="red"></span>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>硬盘使用状况</td>
                    <td colspan="3">
                        <div id="hddstatus" style="width: 100%; height: 200px;"></div>
                    </td>
                </tr>
                </tbody>
            </table>
        <?php } ?>
        <?php if ($svrInfo['nBool']) { ?>
            <table class="sui-table table-bordered table-primary">
                <thead>
                <tr>
                    <th colspan="4">网络使用状况</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="text-center" width="15%">设备</td>
                    <td class="text-center" width="35%">接收</td>
                    <td class="text-center" width="35%">发送</td>
                    <td class="text-center" width="15%">错误/丢失</td>
                </tr>
                <?php foreach ($svrInfo['net'] as $key => $value) { ?>
                    <tr>
                        <td class="text-center"><?php echo $key; ?></td>
                        <td class="text-center">
                            <span id="<?php echo $value['name']; ?>_rxbytes"><?php echo $value['rxbytes']; ?></span>
                            (<span id="<?php echo $value['name']; ?>_rxspeed" class="stxt"></span>)
                        </td>
                        <td class="text-center">
                            <span id="<?php echo $value['name']; ?>_txbytes"><?php echo $value['txbytes']; ?></span>
                            (<span id="<?php echo $value['name']; ?>_txspeed" class="stxt"></span>)
                        </td>
                        <td class="text-center"><span
                                id="<?php echo $value['name']; ?>_errors"><?php echo $value['errors']; ?></span> / <span
                                id="<?php echo $value['name']; ?>_drops"><?php echo $value['drops']; ?></span></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>
</body>
</html>
