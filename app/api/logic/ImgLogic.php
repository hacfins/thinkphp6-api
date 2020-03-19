<?php
namespace app\api\logic;

/*
 * 图像处理类
 */
class ImgLogic extends BaseLogic
{
    /**
     * 生成二维码
     *
     * @param string $data 二维码内容
     *
     * @return bool|string
     */
    public static function QrCode(string $data)
    {
        $path = qrcode($data);
        if(!is_file($path))
        {
            self::$_error_code = \EC::FILE_NOTEXIST_ERROR;
            return false;
        }

        return self::RtnPic($path);
    }
}