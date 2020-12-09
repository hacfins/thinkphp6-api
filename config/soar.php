<?php

/**
 * soar 配置文件
 */
return [
    // soar 路径
    '-soar-path'   => ROOT_PATH . 'tool/soar.linux-amd64',

    // 测试环境配置
    '-test-dsn'    => [
        'host'     => config('database.connections.mysql.hostname'),
        'port'     => config('database.connections.mysql.hostport'),
        'dbname'   => config('database.connections.mysql.database'),
        'username' => config('database.connections.mysql.username'),
        'password' => config('database.connections.mysql.password'),
        'disable'  => false,
    ],

    // 日志输出文件
    '-log-output'  => app()->getRuntimePath()."log/soar.log",

    // 日志级别: [0=>Emergency, 1=>Alert, 2=>Critical, 3=>Error, 4=>Warning, 5=>Notice, 6=>Informational, 7=>Debug]
    '-log-level'   => 7,
    // 报告输出格式: [markdown, html, json]
    '-report-type' => 'html',
];
