<?php

return [
    'default'     => 'sync',
    'connections' => [
        'sync'     => [
            'type' => 'sync',
        ],
        'database' => [
            'type'  => 'database',
            'queue' => 'default',
            'table' => 'jobs',
        ],
        'redis'    => [
            'type'       => 'redis',
            'queue'      => 'default',
            'host'       => '127.0.0.1',
            'port'       => 6379,
            'password'   => '',
            'select'     => 0,
            'timeout'    => 0,
            'persistent' => false,
        ],
    ],
    'failed'      => [
        'type'  => 'none',
        'table' => 'failed_jobs',
    ],
];
