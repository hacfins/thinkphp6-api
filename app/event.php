<?php
// +----------------------------------------------------------------------
// | 事件定义
// +----------------------------------------------------------------------

return [
    'bind'      => [
    ],

    'listen'    => [
        // 应用初始化
        'AppInit'  => [
            'app\\common\\event\\AppInit',
        ],
        // 响应发送
        'HttpRun'  => [
            //'app\\common\\event\\HttpRun',
        ],
        //路由加载完成
        'RouteLoaded' => [
            //'app\\common\\event\\RouteLoaded',
        ],
        // 输出结束-当前响应对象实例
        'HttpEnd'  => [
            'app\\common\\event\\HttpEnd',
        ],
        //日志write方法标签位-当前写入的日志信息
        'LogLevel' => [
            //'app\\common\\event\\LogLevel',
        ],
    ],

    'subscribe' => [
    ],
];
