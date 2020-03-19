<?php
namespace app\api\logic;

use app\api\model\
{
    common\Img
};
use think\facade\Filesystem;
use think\facade\Request;

/*
 * 上传类
 */
class UploadLogic extends BaseLogic
{
    /**
     * 简单文件上传
     *
     * @param string $fid      文件UUID
     * @param array  $validate tp5上传的校验规则
     *
     * @return string 文件的完整路径(带后缀)
     */
    public static function UploadSimple($fid, $validate = [], $dir = '')
    {
        $files = Request()->file();
        if(!$files)
        {
            static::$_error_code = \EC::UPL_VOID;
            return false;
        }

        $file  = reset($files);
        if (!$file)
        {
            static::$_error_code = \EC::UPL_UPLOAD_ERROR;
            return false;
        }

        try
        {
            //1.0 检查文件是否合规
            validate($validate)->check(['file' => $file]);

            //2.0 生成目标目录与文件名
            $saveDir  = dir_sub_date($dir, false);
            $ext = $file->extension();
            if($ext)
            {
                $fid .= ".$ext";
            }

            //3.0 移动文件
            $savePath = Filesystem::putFileAs($saveDir, $file, $fid);
        }
        catch (\Throwable $e)
        {
            static::$_error_code = \EC::UPL_UPLOAD_ERROR;
            static::$_error_msg  = $e->getMessage();
            return false;
        }

        return DIRECTORY_SEPARATOR . $savePath;
    }

    /**
     * Upload
     *
     * @return string
     */
    public static function UploadImg($dir = '')
    {
        //过滤OPTIONS请求
        if (Request::server('REQUEST_METHOD') == 'OPTIONS')
        {
            return false;
        }

        $fid = filename_microtime();

        //上传
        $fileSize = 1024 * 1024 * 2;
        $filePath = self::UploadSimple($fid, [
            'file' => "fileSize:$fileSize|fileExt:jpg,bmp,jpeg,png,gif"],
            $dir);

        if (!$filePath)
            return false;

        //解决图片旋转问题
        $extName = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (($extName == 'jpg') || ($extName == 'jpeg'))
        {
            $img = new Img($filePath);
            $img->ImgRotateAuto($filePath, '#000', false);
        }

        //权限修改为只读
        chmod($filePath, 0744);

        return $filePath;
    }
}