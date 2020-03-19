<?php
namespace app\api\logic;

use app\common\third\CloudAuth;
use think\facade\{
    Cache
};

use string\P4String;

/**
 * 产品授权
 */
class ProductLogic extends BaseLogic
{
    private const CONFIG_LICENSE = LICENSE_FILE;  //授权文件
    private const MACHINECODE    = '8192bc32-2d22-4c32-a587-0abf805c7916'; //初始版本的机器码

    //----------------------------------------------- 获取与激活 -------------------------------------------------------//
    /***
     * API - 读取授权信息
     *
     * @param $licenseArr
     *
     * @return int
     */
    public static function ReadFile()
    {
        try
        {
            if (!is_file(self::CONFIG_LICENSE))
            {
                static::$_error_code = \EC::AUTH_FILE_NOTEXIST_ERROR;
                return false;
            }

            $rContent   = file_get_contents(self::CONFIG_LICENSE);
            $content    = \PhpCrypt::PHP_Decrypt($rContent);

            $contentArr = P4String::jsondecode($content);

            //检测是否是初始文件
            if(isset($contentArr['machinecode']))
            {
                $machinecode = $contentArr['machinecode'];
                if($machinecode == self::MACHINECODE)
                {
                    $realMachine = '';
                    $rtn = self::GetMachinecode($realMachine);
                    if(!$rtn)
                    {
                        return false;
                    }

                    self::InitFile($realMachine);
                }
            }

            return $contentArr;
        }
        catch (\Throwable $e)
        {
            static::$_error_code = \EC::AUTH_FILE_READ_ERROR;
            static::$_error_msg  = $e->getMessage();
            return false;
        }
    }

    /**
     * API - 激活
     *
     * @param string $activecodeInput 激活码
     * @param string $registertoInput 注册者
     *
     * @return int|mixed
     */
    public static function Active(string $activecodeInput, string $registertoInput)
    {
        //1.0 读取license文件
        $licenseArr = self::ReadFile();
        if (!$licenseArr)
        {
            return false;
        }

        $productname    = $licenseArr['productname'];
        $productversion = $licenseArr['productversion'];
        $machinecode    = $licenseArr['machinecode'];
        $activecode     = $licenseArr['activecode'];
        $registerto     = $licenseArr['registerto'];
        $expiretime     = $licenseArr['expiretime'];

        if (!isset($productname) || !isset($productversion) || !isset($machinecode) || !isset($activecode) || !isset($registerto) || !isset($expiretime))
        {
            static::$_error_code = \EC::AUTH_FILE_ERROR;
            return false;
        }
        if ($productname != AUTH_PRODUCT_NAME)
        {
            static::$_error_code =  \EC::AUTH_FILE_ERROR;
            return false;
        }

        //2.0 读取机器码
        $machinecodeTemp = '';
        $rtn             = self::GetMachinecode($machinecodeTemp);
        if (!$rtn)
        {
            return false;
        }

        //3.0 分析授权文件
        //机器码是否一致 -- 说明篡改
        if ($machinecode != $machinecodeTemp)
        {
            static::$_error_code =  \EC::AUTH_MACHINESCODE_ERROR;
            return false;
        }

        //激活码一致
        if ($activecode == $activecodeInput)
        {
            static::$_error_code =  \EC::AUTH_ACTIVECODE_ACTIVE_ERROR;
            return false;
        }

        //激活码是否正确
        $expiretime = static::CheckActiveCode($activecodeInput, $machinecode);
        if(false === $expiretime)
        {
            static::$_error_code =  \EC::AUTH_ACTIVECODE_ERROR;
            return false;
        }

        //注册写入DB，探视是否已经被别人注册了
        if(!yaconf('switch.offline'))
        {
            $rtn = (new CloudAuth())->AddAuth($machinecode, $activecodeInput, $registertoInput);
            if ($rtn['code'] != 200)
            {
                self::$_error_code = $rtn['code'];
                return false;
            }

            $resRcd = $rtn['result'];
            $licenseArr['expiretime'] = $resRcd['expiretime'];
        }
        else
        {
            $licenseArr['expiretime'] = $expiretime;
        }

        //写入文件
        $licenseArr['activecode'] = $activecodeInput;
        $licenseArr['registerto'] = $registertoInput;

        return self::DumpFile($licenseArr);
    }

    //----------------------------------------------- 解析 ------------------------------------------------------------//
    /**
     * 初始化文件
     */
    private static function InitFile($machinecode)
    {
        //写入基本信息
        $licenseArr = array(
            'productname'    => AUTH_PRODUCT_NAME,
            'productversion' => AUTH_PRODUCT_VERSION,
            'machinecode'    => $machinecode,
            'activecode'     => '',
            'registerto'     => '',
            'expiretime'     => '',
            'rules'          => ''
        );

        return self::DumpFile($licenseArr);
    }

    /**
     * 写入授权文件
     *
     * @param     $licenseArr
     * @param int $mod
     *
     * @return int
     */
    private static function DumpFile($licenseArr, $mod = 0755)
    {
        try
        {
            if (!is_file(self::CONFIG_LICENSE))
            {
                static::$_error_code = \EC::AUTH_FILE_NOTEXIST_ERROR;
                return false;
            }

            $tmpFile = tempnam(dirname(self::CONFIG_LICENSE), basename(self::CONFIG_LICENSE));

            $content = P4String::jsonencode($licenseArr);
            $content = \PhpCrypt::PHP_Encrypt($content);

            if (false !== @file_put_contents($tmpFile, $content))
            {
                // rename does not work on Win32 before 5.2.6
                if (@rename($tmpFile, self::CONFIG_LICENSE))
                {
                    @chmod(self::CONFIG_LICENSE, $mod & ~umask());

                    return true;
                }
            }

            // fail
            unlink($tmpFile);

            static::$_error_code = \EC::AUTH_FILE_WIRTE_ERROR;
            return false;
        }
        catch (\Throwable $e)
        {
            static::$_error_code = \EC::AUTH_FILE_WIRTE_ERROR;
            static::$_error_msg  = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取磁盘分片UUID
     *
     * @param $rMachinecode
     *
     * @return int
     */
    private static function GetMachinecode(& $rMachinecode)
    {
        try
        {
            //docker本身有dev，没有磁盘信息。所以docker 磁盘挂在到dev2中
            $disk = 'dev2';
            $dir  = '/dev2/disk/by-uuid/';
            if (!is_dir($dir))
            {
                $disk = 'dev';
                $dir  = '/dev/disk/by-uuid/';
            }

            $dh = opendir($dir);
            while ($file = readdir($dh))
            {
                if (is_link($dir . $file))
                {
                    $path  = realpath($dir . $file);
                    $point = yaconf('storage.point');
                    if (($path == "/{$disk}/{$point}") || ($path == "/{$disk}/vda1")) //实盘或虚拟盘
                    {
                        //原始机器码作简单处理
                        $code         = strtoupper(strrev($file));
                        $len          = strlen($code);
                        $resultString = '';
                        for ($i = 0; $i < $len; $i++)
                        {
                            $resultString .= chr(ord($code[$i]) + ($i % 4));
                        }

                        $rMachinecode = str_replace(array(
                            '+',
                            '/',
                            '=',
                            '<',
                            '>',
                            ':',
                            '.',
                        ), array(
                            '-',
                            '-',
                            '-',
                            'L',
                            'G',
                            'S',
                            '-',
                        ), $resultString);

                        closedir($dh);
                        return true;
                    }
                }
            }
            closedir($dh);
        }
        catch (\Throwable $e)
        {
            closedir($dh);

            static::$_error_code = \EC::AUTH_MACHINESCODE_GET_ERROR;
            static::$_error_msg  = $e->getMessage();
            return false;
        }

        static::$_error_code =  \EC::AUTH_MACHINESCODE_GET_ERROR;
        return false;
    }

    /**
     * 根据机器码、过期时间 获取激活码
     *
     * @param $machinecode
     * @param $expiretime
     * @param $rActivecode
     *
     * @return int
     */
    private static function GetActiveCode($machinecode, $expiretime, & $rActivecode)
    {
        if (!isset($machinecode))
        {
            static::$_error_code = \EC::AUTH_MACHINESCODE_ERROR;
            return false;
        }

        //product name + machine code + expiretime ==> 激活码
        $rActivecode = \PhpCrypt::PHP_Encrypt(AUTH_PRODUCT_NAME . $machinecode . $expiretime);

        return true;
    }

    private static function CheckActiveCode(string $activeCode, string $machinecode)
    {
        //product name + machine code + expiretime ==> 激活码
        $sorInfo = \PhpCrypt::PHP_Decrypt($activeCode);
        if (strpos($sorInfo, AUTH_PRODUCT_NAME . $machinecode) !== 0)
        {
            return false;
        }

        return substr($sorInfo, strlen(AUTH_PRODUCT_NAME . $machinecode));
    }

    //----------------------------------------------- Cache ----------------------------------------------------------//
    private static function Cache_Set()
    {
        Cache::set(CACHE_AUTH_PRODUCT, 1, CACHE_AUTH_PRODUCT_TIME);
    }

    private static function Cache_Get()
    {
        $val = Cache::get(CACHE_AUTH_PRODUCT);
        if ($val)
        {
            return $val == 1;
        }

        return false;
    }

    //----------------------------------------------- Cache ----------------------------------------------------------//
    public static function Check()
    {
        try
        {
            //Cache
            if (self::Cache_Get())
            {
                return true;
            }

            //1.0 读取license文件
            $licenseArr = self::ReadFile();
            if (!$licenseArr)
            {
                return false;
            }

            $productname    = $licenseArr['productname'];
            $productversion = $licenseArr['productversion'];
            $machinecode    = $licenseArr['machinecode'];
            $activecode     = $licenseArr['activecode'];
            $registerto     = $licenseArr['registerto'];
            $expiretime     = $licenseArr['expiretime'];

            if (!isset($productname) || !isset($productversion) || !isset($machinecode) || !isset($activecode) || !isset($registerto) || !isset($expiretime))
            {
                static::$_error_code = \EC::AUTH_FILE_ERROR;
                return false;
            }
            if ($productname != AUTH_PRODUCT_NAME)
            {
                static::$_error_code = \EC::AUTH_FILE_ERROR;
                return false;
            }

            //2.0 读取机器码
            $machinecodeTemp = '';
            $rtn = self::GetMachinecode($machinecodeTemp);
            if (!$rtn)
            {
                return false;
            }

            //3.0 分析授权文件
            //第一次使用
            if ($machinecode == self::MACHINECODE)
            {
                $machinecode = $machinecodeTemp;

                return self::InitFile($machinecode);
            }
            else//第二次使用
            {
                //机器码是否一致 -- 说明篡改
                if ($machinecode != $machinecodeTemp)
                {
                    static::$_error_code = \EC::AUTH_MACHINESCODE_ERROR;
                    return false;
                }

                //激活码是否一致
                if ($activecode == '')
                {
                    static::$_error_code = \EC::AUTH_ACTIVECODE_NOTEXIST;
                    return false;
                }

                $activecodeTemp = '';
                $rtn            = self::GetActiveCode($machinecode, $expiretime, $activecodeTemp);
                if (!$rtn)
                {
                    return false;
                }

                if ($activecode != $activecodeTemp)
                {
                    static::$_error_code = \EC::AUTH_ACTIVECODE_ERROR;
                    return false;
                }

                //是否过期
                if ($expiretime != '0')
                {
                    //Todo： 时间不准
                    if ($expiretime < time())
                    {
                        static::$_error_code = \EC::AUTH_EXPIRE_ERROR;
                        return false;
                    }
                }

                //Cache
                self::Cache_Set();

                return true;
            }
        }
        catch (\Throwable $e)
        {
            static::$_error_code = \EC::AUTH_NOT_EXIST;
            static::$_error_msg  = $e->getMessage();
            return false;
        }
    }
}