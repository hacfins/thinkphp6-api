<?php

namespace command;

use think\image\Exception;

/**
 * FFmpeg 工具类
 *  依赖 ffmpeg、ffprobe、MP4Box 等命令行
 */
class FFmpegTool
{
    private $_ffmpeg  = null;
    private $_ffprobe = null;

    public static function GetInstance(string $path = null)
    {
        static $inst = null;
        if ($inst == null)
        {
            $inst = new self($path);
        }

        return $inst;
    }

    public function __construct(string $path = null)
    {
        $this->_ffmpeg = \FFMpeg\FFMpeg::create([
            'ffmpeg.binaries'  => $path . 'ffmpeg',
            'ffprobe.binaries' => $path . 'ffprobe',
            'ffmpeg.timeout'   => 60,
            'ffprobe.timeout'  => 15,
            'ffmpeg.threads'   => 1,   // The number of threads that FFMpeg should use
        ]);

        if($this->_ffmpeg)
        {
            $this->_ffprobe = $this->_ffmpeg->getFFProbe();
        }

        return $this;
    }

    /**
     * 截图
     *
     * @param string $videoPath 视频路径
     * @param int    $time      截图时间点
     * @param string $imgDir    存储截图的目录 '/mnt/volume1/imgs/'
     * @param bool   $accurate  是否精确
     *
     * @return bool|string
     */
    public function SaveFrame(string $videoPath, string $time = '00:00:00.000', string $imgDir = '', bool $accurate = true)
    {
        $savePath = $imgDir . md5($videoPath . $time);
        if (is_file($savePath))
            return $savePath;

        if(!is_dir($imgDir))
            mk_dir($imgDir);

        try
        {
            // Open your video file
            $video = $this->_ffmpeg->open($videoPath);

            // Get frame
            $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromString($time));

            // Save frame
            $frame->save($savePath . '.jpg', $accurate);

            // rename file
            if(is_file($savePath . '.jpg'))
                rename($savePath . '.jpg', $savePath);

            return $savePath;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * 截取文件
     * 
     * @param string $file      源文件路径
     * @param string $desPath   目的文件路径
     * @param string $startTime 开始时间
     * @param string $endTime   结束时间
     *
     * @return bool
     */
    public function ClipFromSameCodecs(string $file, string $desPath, string $startTime = '00:00:00.000', string $endTime = '00:00:00.000')
    {
        try
        {
            $command = TOOL_FFMPEG . "ffmpeg -y -threads 1 -i {$file}  -vcodec copy -acodec copy -ss {$startTime} -to {$endTime} {$desPath}";
            exec($command, $output, $return_var);
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * H264Isma
     *
     * @date
     *
     * @param string $filePath 文件路径
     *
     * @return void
     */
    public static function H264Isma($filePath)
    {
        //将H264文件的文件头移动到文件尾
        $output = null;
        {
            //MP4Box -isma test.mp4
            try
            {
                $cmd    = TOOL_MP4BOX . 'MP4Box  -isma ' . "{$filePath}" . ' 2>&1';
                $output = shell_exec($cmd);
            }
            catch (\Exception $e)
            {

            }
        }
    }

    /**
     * 获取视频信息
     *
     * @param string $file 文件全路径
     *
     * @return array|bool
     */
    public function GetInfo(string $file)
    {
        try
        {
            return  $this->_ffprobe->format($file)->all();
        }
        catch (Exception $e)
        {
            return false;
        }

//---- Old Method
//        $command = sprintf(TOOL_FFMPEG . 'ffmpeg -i "%s" 2>&1', $file);
//
//        ob_start();
//        passthru($command);
//        $info = ob_get_contents();
//        ob_end_clean();
//
//        $data = array();
//        if (preg_match('/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/', $info, $match))
//        {
//            $data['duration'] = $match[1]; //播放时间
//            $arr_duration     = explode(':', $match[1]);
//            $data['seconds']  = $arr_duration[0] * 3600 + $arr_duration[1] * 60 + $arr_duration[2]; //转换播放时间为秒数
//            $data['start']    = $match[2]; //开始时间
//            $data['bitrate']  = $match[3]; //码率(kb)
//        }
//        if (preg_match('/Video: (.*?), (.*?), (.*?)[,\s]/', $info, $match))
//        {
//            $data['vcodec']     = $match[1]; //视频编码格式
//            $data['vformat']    = $match[2]; //视频格式
//            $data['resolution'] = $match[3]; //视频分辨率
//            $arr_resolution     = explode('x', $match[3]);
//            $data['width']      = $arr_resolution[0]??null;
//            $data['height']     = $arr_resolution[1]??null;
//        }
//        if (preg_match('/Audio: (\w*), (\d*) Hz/', $info, $match))
//        {
//            $data['acodec']      = $match[1]; //音频编码
//            $data['asamplerate'] = $match[2]; //音频采样频率
//        }
//        if (isset($data['seconds']) && isset($data['start']))
//        {
//            $data['play_time'] = $data['seconds'] + $data['start']; //实际播放时间
//        }
//        $data['size'] = filesize($file); //文件大小
//
//        return $data;
    }
}