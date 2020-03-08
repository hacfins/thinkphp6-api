<?php
// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

$redisOptions = yaconf('redis');
$redisSessionOptions = yaconf('redis_session');

return [
    // 默认缓存驱动
    'default' => yaconf('cache.driver', 'file'),

    // 缓存连接方式配置
    'stores'  => [
        'redis_cache' => [
            'type'       => 'redis',

            'host'       => $redisOptions['host'],
            'port'       => $redisOptions['port'],
            'password'   => $redisOptions['password'],
            'persistent' => true,

            // 缓存有效期 0表示永久缓存
            'expire'     => 7200,
            // 缓存前缀
            'prefix'     => 'a_',
        ],

        'redis_session' => [
            'type'       => 'redis',

            'host'       => $redisSessionOptions['host'],
            'port'       => $redisSessionOptions['port'],
            'password'   => $redisSessionOptions['password'],
            'persistent' => true,

            // 缓存有效期 0表示永久缓存
            'expire'     => 7200,
            // 缓存前缀
            'prefix'     => 'a_s_',
        ],

        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => '',
            // 缓存前缀
            'prefix'     => 'a_',
            // 缓存有效期 0表示永久缓存
            'expire'     => 7200,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        // 更多的缓存连接
    ],
];
