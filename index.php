<?php
// [ 应用入口文件 ]
//**开启严格模式
declare(strict_types=1);

namespace think;

require __DIR__ . '/vendor/autoload.php';

// 定义根目录
define('ROOT_PATH', __DIR__ . '/');

// **定义配置文件目录和应用目录同级
define('CONF_PATH', __DIR__ . '/config/extra/');

// ***引入错误码
require CONF_PATH . '/EC.php';

// ***引入常量定义
require CONF_PATH . '/define.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();
$response->send();
$http->end($response);