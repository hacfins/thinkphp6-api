<?php
namespace app\api\model\common;

use app\common\third\Apache;
use Carbon\Carbon;
use image\Imagick2;
use think\facade\Request;

/**
 * 图像处理
 */
class Img
{
    // 图片信息
    private $_img    = null;
    public  $_width  = null;
    public  $_height = null;
    public  $_type   = null; //png|gif|bmp|...
    public  $_mime   = null;

    /**
     * Img constructor.获取图片基本信息
     *
     * @param string $file
     */
    public function __construct($file)
    {
        $this->_file = $file;

        $this->_img    = new Imagick2($this->_file);
        $this->_type   = $this->_img->type();
        $this->_mime   = $this->_img->mime();
        $this->_width  = $this->_img->width();
        $this->_height = $this->_img->height();
    }

    //--------------------------------------- 返回图像数据 ----------------------------------------------------------//
    /**
     * 直接向浏览器输出图片
     *
     * @author jiangjiaxiong
     *
     * @param string $file 文件的完整路径
     *
     * +---------------------------------------------------------+
     * |    打开页面方式          |      状态                       |
     * +---------------------------------------------------------+
     * | 1. 第一次打开页面        | 200，简单直接地从缓存加载
     * | 2. 重启浏览器打开页面     | cache，即时发生资源修改也不会重新请求
     * | 3. F5刷新              | 304，发生修改的资源状态为200
     * | 4. Ctrl+F5刷新         | 200，强制全新请求
     * | 5. 后退                | cache，简单直接地从缓存加载
     * | 6. 在已访问页面地址栏回车  | cache，简单直接地从缓存加载
     * +---------------------------------------------------------+
     *
     * @return void
     */
    public static function RtnPic($file)
    {
        if (!is_readable($file))//whether a file exists and is readable
        {
            E(\EC::FILE_NOTEXIST_ERROR);
        }

        $info = getimagesize($file); //get mime info
        if (!$info)
        {
            E(\EC::FILE_NOTEXIST_ERROR);
        }

        $filemtime = filemtime($file);
        $filesize  = filesize($file);

        header("Accept-Ranges: bytes");
        header("Content-type: {$info['mime']}");
        header('Content-Length: ' . $filesize);

        // 图片缓存30天
        // 当请求数据在有效期内时客户端浏览器从缓存请求数据而不是服务器端
        header('Expires: ' . gmdate ('D, d M Y H:i:s', time() + 2592000). ' GMT', true);

        // checking if the client is validating his cache and if it is current.
        $modifiedSince = Request::server('HTTP_IF_MODIFIED_SINCE');
        if ($modifiedSince && (strtotime($modifiedSince) == $filemtime))
        {
            // client's cache is current, so we just respond '304 Not Modified'.
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filemtime) . ' GMT', true, 304);
        }
        else
        {
            // Image not cached or cache outdated, we respond '200 OK' and output the image.
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filemtime) . ' GMT', true, 200);

            $fp = fopen($file, 'rb'); //open by rb
            fpassthru($fp); //dump the picture && output to browse
            fclose($fp);
        }
    }

    /**
     * 返回图片的防盗链地址
     *
     * @author jiangjiaxiong
     *
     * @param string $file 文件的完整路径
     *
     * @return string
     */
    public static function RtnUrl($file)
    {
        return Apache::Get_AuthToken_URI($file);
    }

    /**
     * 调整图片尺寸
     *
     * @author jiangjiaxiong
     * @date
     *
     * @param string      $file    原图的路径
     * @param int         $Dw      调整时最大宽度;缩略图时的绝对宽度
     * @param int         $Dh      调整时最大高度;缩略图时的绝对高度
     * @param string|null $newFile 新图片的路径（如果为空，会覆盖原图）
     *
     * @return string 新图片的路径
     */
    public static function Resize(string $file, int $Dw = 450, int $Dh = 450, string $newFile = null)
    {
        try
        {
            $newFile = $newFile ?? $file;

            $image = new Imagick2($file);
            $image->thumb($Dw, $Dh);
            $image->save($newFile);
        }
        catch (\Throwable $e)
        {
            E(\EC::API_ERR, '图片文件加载失败');
        }

        return $newFile;
    }

    /**
     * 返回图片的base64编码
     *
     * @author jiangjiaxiong
     *
     * @param string $filePath 文件的完整路径
     * @param bool   $compress 是否压缩尺寸
     *
     * @return string
     */
    public static function RtnBase64($filePath, $compress = true)
    {
        if (!is_file($filePath))
        {
            E(\EC::FILE_NOTEXIST_ERROR);
        }

        $fInfo    = new \FInfo(FILEINFO_MIME_TYPE);
        $fileMime = $fInfo->file($filePath);

        $base64 = 'data:' . $fileMime . ';base64,' . base64_encode(file_get_contents($filePath));
        $size   = strlen($base64);

        //压缩数据，使其小于32K (为了兼容IE8以及提高性能)
        if ($compress)
        {
            $fixSize = 1024 * 32 - 2;
            $ext     = pathinfo($filePath, PATHINFO_EXTENSION);

            if ($size >= $fixSize)
            {
                //临时文件
                $tmpDir = DIR_TEMPS_IMGS . Carbon::now()->toDateString() . DIRECTORY_SEPARATOR;
                if(!is_dir($tmpDir))
                    mk_dir($tmpDir);

                $tmpFile = $tmpDir . uniqid() . ($ext ? ".$ext" : '');
                copy($filePath, $tmpFile);

                $width = 750;
                while (true)
                {
                    if ($size < $fixSize || $width < 50)
                    {
                        break;
                    }

                    //压缩图片
                    self::Resize($tmpFile, $width, $width);

                    $base64 = 'data:' . $fileMime . ';base64,' . base64_encode(file_get_contents($tmpFile));
                    $size   = strlen($base64);

                    $width -= 50;
                }

                //删除临时文件
                unlink($tmpFile);
            }
        }

        return $base64;
    }

    //--------------------------------------- 缩略图/裁剪 ----------------------------------------------------------//
    /**
     * 生成缩略图
     *
     * @author jiangjiaxiong
     *
     * @param string      $file      图片路径
     * @param int         $width     缩略图的宽
     * @param int         $height    缩略图的高
     * @param string|null $newFile   缩略图的路径 （如果为空，会覆盖原图）
     * @param bool        $bUseAlpha 使用Alpha通道
     *
     * @return string 缩略图的路径
     */
    public static function MakeThumb(string $file, int $width, int $height, string $newFile = null, bool $bUseAlpha = true)
    {
        $thumbFile = $newFile ?? $file;
        $imagick   = null;
        $ext       = pathinfo($file, PATHINFO_EXTENSION);

        //超过一定尺寸，不生成缩略图
        $sorFileSize = filesize($file);
        if($sorFileSize > BROWSE_IMG_THUMB_FILESIZE_MAX)
            return false;

        if (isset($ext) && (strtolower($ext) == 'gif'))//GIF图片使用命令行执行
        {
            //Imagick打开超大的图片时，特别耗时（如50M的GIF图片）
            goto GIFFun;
        }
        else
        {
            try
            {
                $imagick = new \Imagick ($file);
            }
            catch (\Throwable $e)
            {
                E(\EC::API_ERR, '图片文件加载失败');
            }

            $imgType = strtolower($imagick->getImageFormat());
            if ($imgType == 'ico')
            {
                copy($file, $thumbFile);

                $imagick->clear();

                return $newFile;
            }

            if ($imgType != 'gif')
            {
                //去除图片信息
                $imagick->stripImage();
                //生成缩略图
                $imagick->ThumbnailImage($width, $height, true);

                if ($bUseAlpha && ($imgType == 'png'))//1.0 压缩png图片
                {
                    $imagick->setImageFormat('PNG');
                    $imagick->setImageCompression(\Imagick::COMPRESSION_UNDEFINED);
                    $imagick->setImageCompressionQuality(0);
                    $imagick->setOption('png:format', 'png16');
                }
                else//2.0 压缩jpeg
                {
                    if ($imgType == 'png')/* 按照缩略图大小创建一个白色背景的图片 */
                    {
                        $canvas = new \Imagick();
                        $canvas->newImage($imagick->getImageWidth(), $imagick->getImageHeight(), 'white', 'jpg');
                        /* 合并图片  */
                        $canvas->compositeImage($imagick, \imagick::COMPOSITE_OVER, 0, 0);
                        $imagick->clear();

                        $imagick = $canvas;
                    }

                    $imagick->setImageFormat('JPEG');
                    $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $imagick->setImageCompressionQuality(75);
                }

                $imagick->writeImage($thumbFile);
            }
            else
            {
                GIFFun:
                try
                {
                    //小于2M的gif才生成动态缩略图
                    if ($sorFileSize <= BROWSE_IMG_THUMB_GIF_FILESIZE_MAX)
                    {
                        $img2 = new Imagick2($file);
                        $img2->thumb($width, $height);
                        $img2->save("{$thumbFile}.gif", 'gif', 75);

                        rename($thumbFile . '.gif', $thumbFile);
                    }
                    else
                    {
                        $cmd = sprintf(TOOL_IMAGEMAGICK . "convert {$file}[0] -resize {$width}x{$height} {$thumbFile}.jpg");
                        shell_exec($cmd);
                        rename($thumbFile . '.jpg', $thumbFile);
                    }
                }
                catch (\Throwable $e)
                {
                    E($e->getCode(), $e->getMessage());
                }
            }

            if ($imagick)
            {
                $imagick->clear();
            }
        }

        return $thumbFile;
    }

    /**
     * 纠正图片旋转问题
     *
     * @author jiangjiaxiong
     * @date
     *
     * @param string $file         文件路径
     * @param string $background   底色
     * @param bool   $saveOriginal 保留原始文件
     *
     * @return void
     */
    public static function ImgRotateAuto(string $file, string $background = '#000', bool $saveOriginal=true)
    {
        if (!is_file($file))
        {
            return;
        }

        $imagick     = new \Imagick($file);
        $orientation = $imagick->getImageOrientation();

        if ($orientation)
        {
            switch ($orientation)
            {
                case \Imagick::ORIENTATION_BOTTOMRIGHT:
                    $imagick->rotateimage($background, 180); // rotate 180 degrees
                    break;
                case \Imagick::ORIENTATION_RIGHTTOP:
                    $imagick->rotateimage($background, 90); // rotate 90 degrees CW
                    break;
                case \Imagick::ORIENTATION_LEFTBOTTOM:
                    $imagick->rotateimage($background, -90); // rotate 90 degrees CCW
                    break;
                default:
                    goto end;
                    break;
            }

            //保留原始文件
            if($saveOriginal)
                copy($file, $file . FILE_SUFFIX_RAW);

            $imagick->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
            $imagick->writeImage($file);
        }

        end:
        $imagick->clear();
    }

    /**
     * 批量创建缩略图 (详见下注释)
     * 生成的缩略图会存放在 '原路径+thumb/' 下 缩略图精度即缩略图的文件名
     * eg: /var/pic1 生成的缩略图可能是/var/pic1_thumb/96、/var/pic1_thumb/200、/var/pic1_thumb/400
     *
     * @author jiangjiaxiong
     *
     * @param string $file 文件的完整路径
     * @param string $type pic,图片;ps,头像
     *
     * @return array 缩略图的路径
     */
    public static function ThumbCreate($file, $type = 'pic')
    {
        if (!is_file($file))
        {
            E(\EC::FILE_NOTEXIST_ERROR);
        }

        $pathInfo = pathinfo($file);
        $dirName  = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_thumb' . DIRECTORY_SEPARATOR;
        if (!is_dir($dirName))
        {
            if (!mk_dir($dirName))
            {
                E(\EC::DIR_MK_ERR);
            }
        }

        $data = [];
        if ($type === 'pic')
        {
            $data[THUMBER_MIDDLE] = self::MakeThumb($file, THUMBER_MIDDLE, THUMBER_MIDDLE, $dirName . THUMBER_MIDDLE);
            $data[THUMBER_SMALL] = self::MakeThumb($file, THUMBER_SMALL, THUMBER_SMALL, $dirName . THUMBER_SMALL);
        }
        elseif ($type === 'ps')
        {
            $data[THUMBER_SMALL] = self::MakeThumb($file, THUMBER_SMALL, THUMBER_SMALL, $dirName . THUMBER_SMALL);
            $data[THUMBER_MINI] = self::MakeThumb($file, THUMBER_MINI, THUMBER_MINI, $dirName . THUMBER_MINI);
            $data[THUMBER_MINI_PHOTE] = self::MakeThumb($file, THUMBER_MINI_PHOTE, THUMBER_MINI_PHOTE, $dirName . THUMBER_MINI_PHOTE);
        }

        return $data;
    }

    /**
     * 图像裁剪(不影响原图,详见下注释)
     * 剪切后的图片会放在 '原路径+crop/$width.'_'.$height.'_'.$left.'_'.$top' 下
     * eg: ./img/picture 的裁剪图片就是./img_crop/100_100_20_30
     *
     * @author jiangjiaxiong
     *
     * @param string $file   文件的完整路径
     * @param int    $width  宽
     * @param int    $height 高
     * @param int    $left   起始位置右移(像素)
     * @param int    $top    起始位置下移(像素)
     *
     * @return string 裁剪的图片的完整路径
     */
    public static function ImgCrop($file, $width, $height, $left = 0, $top = 0)
    {
        $pathInfo = pathinfo($file);
        $dirName  = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_crop' . DIRECTORY_SEPARATOR;
        if (!is_dir($dirName))
        {
            if (!mk_dir($dirName))
            {
                E(\EC::DIR_MK_ERR);
            }
        }
        $fileName = $width . '_' . $height . '_' . $left . '_' . $top;
        $path     = $dirName . $fileName;

        try
        {
            $image = new Imagick2();
            $image->open($file);
            $image->crop($width, $height, $left, $top);
            $image->save($path);
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }

        return $path;
    }

    //--------------------------------------- 格式转换 ----------------------------------------------------------//
    /**
     * Ico2Png
     *
     * @author jiangjiaxiong
     * @date
     *
     * @param string      $icoFile
     * @param string|null $pngFile
     *
     * @return void
     */
    public static function Ico2Png(string $icoFile, string $pngFile = null)
    {
        if (!is_file($icoFile))
        {
            E(\EC::API_ERR, '无效的ico文件');
        }

        $newName = $pngFile;
        if (is_null($pngFile))
        {
            $newName = $icoFile;
        }

        $cmd = sprintf(TOOL_FFMPEG . "ffmpeg -i {$icoFile} -s 256x256 {$newName}");
        shell_exec($cmd);
    }
}