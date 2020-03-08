<?php
namespace app\api\logic\traits;

use app\api\model\
{
    common\Img
};
use think\facade\Request;

/*
 * 图像处理
 */
trait ImgProcess
{
    /**
     * output image
     *
     * @param      $filePath
     * @param int  $content_type
     * @param bool $compress
     *
     * @return string
     */
    public static function RtnPic($filePath, $content_type = IMG_RTYPE_PIC, $compress = true)
    {
        $imgPath = '';
        switch ($content_type)
        {
            case IMG_RTYPE_PIC:
                Img::RtnPic($filePath);
                break;
            case IMG_RTYPE_URL:
                $imgPath = Img::RtnUrl($filePath);
                break;
            case IMG_RTYPE_BASE64:
                $imgPath = Img::RtnBase64($filePath, $compress);
                break;
        }

        return $imgPath;
    }

    /**
     * 本地图片带有域名的可访问路径
     *
     * @param string $filePath
     *
     * @return string
     */
    public static function GetImgLocalUrl(string $imgPath)
    {
        if (!is_file($imgPath) || (strpos($imgPath, DIR_IMGS) !== 0))
            return '';

        return Request::domain() . DIRECTORY_SEPARATOR . MODULE_NAME . DIRECTORY_SEPARATOR . substr($imgPath, strlen(DIR_IMGS));
    }

    /**
     * 如果是本地文件，需要上传到远程图片服务器
     *
     * @param string $imgPath
     *
     * @return bool|string
     */
    public static function CheckImgPath(string $imgPath)
    {
        $domain  = Request::domain();
        $port    = Request::port();
        $imgPort = parse_url($imgPath, PHP_URL_PORT) ?? 80;

        if (($port == $imgPort) && (0 == strncasecmp($imgPath, $domain, strlen($domain))))
        {
            $file = DIR_IMGS . substr($imgPath, strlen($domain. DIRECTORY_SEPARATOR . MODULE_NAME) + 1);

            $rtn = img_upload($file, []);
            if ($rtn['code'] != \EC::SUCCESS)
            {
                static::$_error_code = $rtn['code'];
                static::$_error_msg  = $rtn['msg'];

                return false;
            }

            $imgPath = $rtn['result']['path'];
        }

        return $imgPath;
    }
}