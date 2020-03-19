<?php
namespace app\common\third;

use Sinergi\BrowserDetector\{
    Browser
};

/*
 * Apache 扩展服务
 */
class Apache
{
    /**
     * XSendFile 下载文件
     *
     * @param string $path     文件的全路径 eg: /mnt/volume1/files/2016/9/26/12/3e883ca8-154a-4c1c-b6ce-3fe93e6ead07_thumb_400_300.jpg
     * @param string $fileName 显示的名称 eg: 道德经的奥秘.mp4
     *
     * @return int
     */
    public static function Download($path, $fileName = null)
    {
        if (!isset($path) || !is_file($path))
        {
            E(\EC::FILE_NOTEXIST_ERROR);
        }

        $fileSize =  filesize($path);
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header("Cache-Control:public");
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $fileSize);
        header('Accept-Ranges: bytes');
        header('Accept-Length:' . $fileSize);

        //客户端的弹出对话框，对应的文件名
        if (is_null($fileName))
        {
            $fileName = basename($path);
        }

        $browser = new Browser();
        $browserName  = $browser->getName();

        if ($browserName == \Sinergi\BrowserDetector\Browser::IE)
        {
            $encoded_filename = rawurlencode($fileName);
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        }
        else if ($browserName == \Sinergi\BrowserDetector\Browser::FIREFOX)
        {
            header("Content-Disposition: attachment; filename*=\"utf8''" . $fileName . '"');
        }
        else
        {
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
        }

        @header('X-Sendfile: ' . $path);

        return true;
    }

    /*
     * 文件防盗链地址
     *
     * LoadModule auth_token_module  modules/mod_auth_token.so
     * Alias /preview      "/mnt"
     * <Location /preview/>
     * AuthTokenSecret     "s3cr3tstr1ng"
     * AuthTokenPrefix     /preview/
     * AuthTokenTimeout    3600
     * AuthTokenLimitByIp  on
     * </Location>
     */
    public static function Get_AuthToken_URI($sRelPath, string $remoteAddr=null)
    {
        $secret        = 's3cr3tstr1ng';     // Same as AuthTokenSecret
        $protectedPath = '/preview/';        // Same as AuthTokenPrefix
        $ipLimitation  = true;               // Same as AuthTokenLimitByIp
        $hexTime       = dechex(time());     // Time in Hexadecimal

        // �����·��Ϊȫ·�� /mnt/volume1/2015/12/2/18/3/24637b61-a010-49cc-8c2d-6a0005abf2e5
        // ��Ҫ��/volume1/2015/12/2/18/3/24637b61-a010-49cc-8c2d-6a0005abf2e5 ·��
        $fileName = substr($sRelPath, 4); // The file to access

        // Let's generate the token depending if we set AuthTokenLimitByIp
        if ($ipLimitation)
        {
            if(!$remoteAddr)
                $remoteAddr = $_SERVER['REMOTE_ADDR'];
            $token = md5($secret . $fileName . $hexTime . $remoteAddr);
        }
        else
        {
            $token = md5($secret . $fileName . $hexTime);
        }

        // We build the url
        $httpOrigin = null;
        if (isset($_SERVER['HTTP_HOST']))
        {
            $httpOrigin = 'http://' . $_SERVER['HTTP_HOST'];
        }
        else
        {
            $httpOrigin = 'http://' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'];
        }

        $url = $httpOrigin . $protectedPath . $token . '/' . $hexTime . $fileName;

        return $url;
    }
}
