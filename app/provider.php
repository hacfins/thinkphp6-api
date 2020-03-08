<?php
// +----------------------------------------------------------------------
// | 容器Provider定义文件
// +----------------------------------------------------------------------

return [
    'think\Request'          => \app\common\facade\Request::class,
    'think\exception\Handle' => \app\common\exception\ExceptionHandle::class,
];