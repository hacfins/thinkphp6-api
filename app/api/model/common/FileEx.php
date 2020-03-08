<?php
namespace app\api\model\common;

/**
 * 文件扩展类
 */
class FileEx
{
    /**
     * 获取文件类型
     *
     * @param string $filePath 文件的完整路径
     *
     * @return int 类型代码
     */
    public static function GetType(string $filePath)
    {
        if (!is_file($filePath))
        {
            E(\EC::SOURCE_NOT_EXIST_ERROR);
        }

        //根据文件扩展名将mime（application/*）无法识别的文件再分类
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!$ext)
        {
            $fInfo = new \FInfo(FILEINFO_MIME_TYPE);
            $mime  = $fInfo->file($filePath);

            if (strpos($mime, 'image/') !== false)
            {//图片
                return FILE_TYPE_PICTURE;
            }
            elseif (strpos($mime, 'audio/') !== false)
            {//音频
                return FILE_TYPE_AUDIO;
            }
            elseif (strpos($mime, 'video/') !== false)
            {//视频
                return FILE_TYPE_VIDEO;
            }
            else
            {//其他
                return FILE_TYPE_OTHER;
            }
        }
        $ext = strtolower($ext);

        //视频
        $arrayVideo = [
            'mov',
            'mp4',
            'mpe',
            'mpeg',
            'mpg',
            'm4v',
            'mkv',
            'm4d',
            '3gp',
            'avi',
            'flv',
            'wmv',
            'asf',
            'rmvb',
            'rm',
            'ogv',
            'mts',
            'm2ts',
            'ts',

            'asx',
            'dav',
            'dv',
            'mxf',
            'vob',
            'vp6',
            'webm',
        ];
        if (in_array($ext, $arrayVideo))
        {
            return FILE_TYPE_VIDEO;
        }

        //图片
        $arrayPic = [
            'jpeg',
            'jpg',
            'jpe',
            'bmp',
            'png',
            'gif',
            'tif',
            'tiff',
            'ico',
            'tga',
            'pcx',
            'exif',
            'xpm',
            'jif',
        ];
        if (in_array($ext, $arrayPic))
        {
            return FILE_TYPE_PICTURE;
        }

        //文档
        $arrayText = [
            'doc',
            'docx',
            'ppt',
            'pptx',
            'xls',
            'xlsx',
            'csv',
            'rtf',
            'odt',
            'ods',
            'pdf',
            'txt',
            'wps',
        ];
        if (in_array($ext, $arrayText))
        {
            return FILE_TYPE_TEXT;
        }

        //音频
        $arrayAudio = [
            'mp3',
            'aac',
            'aif',
            'aiff',
            'oga',
            'ogg',
            'wav',
            'wma',
            'm4a',
            'flac',
            'ac3',

            'amr',
            'ape',
            'au',
            'mmf',
            'mp2',
            'tak',
            'tta',
            'wv',
        ];
        if (in_array($ext, $arrayAudio))
        {
            return FILE_TYPE_AUDIO;
        }

        return FILE_TYPE_OTHER;
    }

    /**
     * 获取文件的扩展名
     *
     * @param string $filePath 文件的完整路径
     *
     * @return string
     */
    public static function GetExt(string $filePath)
    {
        if (!is_file($filePath))
        {
            E(\EC::SOURCE_NOT_EXIST_ERROR);
        }

        // 直接获取
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if ($ext)
        {
            return $ext;
        }

        //从mime获取
        $fInfo    = new \FInfo(FILEINFO_MIME_TYPE);
        $fileMime = $fInfo->file($filePath);

        $mime = include(CONF_PATH . 'mime.php');

        return $mime[$fileMime] ?? '';
    }

    /**
     * 获取文件类型的名称
     *
     * @param string $file
     *
     * @return string
     */
    public static function GetTypeName(string $filePath)
    {
        $fileType = self::GetType($filePath);

        switch ($fileType)
        {
            case FILE_TYPE_AUDIO:
                return 'audio';
            case FILE_TYPE_VIDEO:
                return 'video';
            case FILE_TYPE_PICTURE:
                return 'pic';
            case FILE_TYPE_TEXT:
                return 'doc';
            case FILE_TYPE_OTHER:
                return 'other';
            default:
                return 'default';
        }
    }

    /**
     * 生成某文件的hash
     *
     * 头部1M + 尾部1M + 文件大小
     *
     * @return null|string
     */
    public static function CreateHash($file)
    {
        if (!is_file($file))
        {
            return false;
        }

        try
        {
            $fsize = filesize($file);

            if ($fsize <= FILE_HASH_MIN_FILE)
            {
                $cnt  = get_from_file($file);
                $hash = md5($cnt);
                unset($cnt);
            }
            else
            {
                $head = get_from_file($file, 0, 1024 * 1024 * 1);
                $tail = get_from_file($file, -1024 * 1024 * 1);

                $hash = md5($head . $tail . $fsize);

                unset($head);
                unset($tail);
            }

            chmod($file, 0777);
        }
        catch (\Throwable $e)
        {
            return false;
        }

        if (strlen($hash) != 32 && strlen($hash) != 96)
        {
            return false;
        }

        return $hash;
    }

    /**
     * 根据模版，获取匹配的文件列表
     *
     * @param     $pattern
     * @param int $type
     *        0x0001 文件
     *        0x0002 文件夹
     * @return array|null
     */
    public static function GlobFile($pattern, $type = 0x0001)
    {
        $results = glob($pattern);
        if (!$results)
        {
            return null;
        }

        $matchArr = array();
        foreach ($results as $path)
        {
            if (($type & 0x0001) == 0x0001) //文件
            {
                if (is_file($path))
                {
                    $matchArr[] = $path;
                }
            }

            if (($type & 0x0002) == 0x0002)
            {
                if (is_dir($path))
                {
                    $matchArr[] = $path;
                }
            }
        }

        if (count($matchArr) < 1)
        {
            return null;
        }

        return $matchArr;
    }
}
