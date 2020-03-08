<?php
// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

return [
    // session name
    'name'           => 'a_session',

    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',

    // 驱动方式 支持file cache
    'type'           => 'cache',

    // 存储连接标识 当type使用cache的时候有效
    'store'          => 'redis_session',

    //-覆盖 store
    //Todo: 过期时间应该为 72 小时 或 30 天（需要修改源码）
    // session 数据的默认缓存时间为 30 天
    'expire'         => USERTOKENS_TOKEN_EXPIRES_LONG,
    // 前缀
    'prefix'         => 'a_s_',
];
