<?php
// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------

return [
    // 默认日志记录通道
    'default'      => yaconf('log.channel', 'file'),
    // 日志记录级别
    'level'        => [
        'error',
        'warning',
        //        'notice',
        //        'info',
        'debug',
        //'sql',
    ],
    // 日志类型记录的通道 ['error'=>'email',...]
    'type_channel' => [],
    // 关闭全局日志写入
    'close'        => false,
    // 全局日志处理 支持闭包
    'processor'    => null,

    // 日志通道列表
    'channels'     => [
        'file' => [
            // 日志记录方式
            'type'           => 'File',
            // 日志保存目录
            'path'           => '',
            // 单文件日志写入
            'single'         => false,
            // 独立日志级别
            'apart_level'    => ['error', 'sql'],
            // 单个日志文件的大小限制，超过后会自动记录到第二个文件
            'file_size'    => 2097152,
            // 最大日志文件数量
            'max_files'      => 30,
            // 使用JSON格式记录
            'json'           => false,
            // 日志处理
            'processor'      => null,
            // 关闭通道日志写入
            'close'          => false,
            'time_format'    => 'Y-m-d H:i:s',
            // 日志输出格式化
            'format'         => '[%s][%s] %s',
            // 是否实时写入
            'realtime_write' => false,
        ],
        // 其它日志通道配置
    ],

];
