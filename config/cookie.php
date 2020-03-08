<?php
// +----------------------------------------------------------------------
// | Cookie设置
// +----------------------------------------------------------------------

//跨子域共享!!!
$domain = domain_top();

return [
    // cookie 保存时间
    'expire'    => 0,
    // cookie 保存路径
    'path'      => '/',
    // cookie 有效域名
    'domain'    => $domain ? $domain : '',
    //  cookie 启用安全传输
    'secure'    => false,
    // httponly设置
    'httponly'  => true
];
