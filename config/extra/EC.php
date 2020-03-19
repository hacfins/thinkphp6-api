<?php

// +----------------------------------------------------------------------
// | api请求返回的错误码
// +----------------------------------------------------------------------
class EC
{
    //错误码
    const SUCCESS   = 200;
    const ERROR_404 = 404;
    const API_ERR   = 500;

    //URL - (401 -- 420)
    const  URL_ERROR                      = 401; //url有误
    const  URL_EXPIRED_ERROR              = 402; //url失效
    const  URL_ACCESSTOKEN_NOTEXIST_ERROR = 403; //url缺少授权信息ACCESS-TOKEN

    //database - (421 -- 440)
    const  DB_CONNECT_ERROR   = 421; //数据库连接失败
    const  DB_OPERATION_ERROR = 422; //数据操作失败
    const  DB_ADD_ERR         = 423; //数据添加失败
    const  DB_RECORD_NOTEXIST = 424; //数据记录不存在

    //USER - (441 -- 460)
    const  USER_PASSWD_ERROR       = 441; //密码错误
    const  USER_NOTLOGIN_ERROR     = 442; //用户未登陆
    const  USER_NOTACTIVE_ERROR    = 443; //用户未激活
    const  USER_NOPERMISSION_ERROR = 444; //用户未授权
    const  USER_NOTEXIST_ERROR     = 445; //用户不存在
    const  USER_EXIST_ERROR        = 446; //用户已经存在
    const  USER_PASSWD_NULL_ERROR  = 447; //密码为null
    const  USER_EMAIL_EXIST_ERROR  = 448; //邮箱已经存在
    const  USER_OLD_PWD            = 449; //新旧密码相同
    const  USER_OLD_PWD_ERR        = 450; //原密码不正确
    const  USER_NEW_PWD_NULL       = 451; //新密码不能为空
    const  USER_PASSWD_SAME_ERROR  = 452; //密码不能和用户名相同

    const  USER_PHONE_NOTEXIST_ERROR  = 454; //手机号不存在
    const  USER_PHONE_EXIST_ERROR     = 455; //手机号已经存在
    const  USER_ADMIN_DELETE_ERROR    = 456; //超级管理员不能禁用
    const  USER_ACTIVE_ERROR          = 457; //用户已激活
    const  USER_DISABLE_ERROR         = 458; //用户已禁用
    const  USER_EMAIL_NOT_EXIST_ERROR = 459; //邮箱不存在

    //ACCESSTOKEN - (461 -- 480)
    const  ACCESSTOKEN_ERROR         = 461; //ACCESS-TOKEN 令牌无效
    const  ACCESSTOKEN_EXPIRED_ERROR = 462; //ACCESS-TOKEN 过期
    const  ACCESSTOKEN_OFFLINE_ERROR = 463; //ACCESS-TOKEN 异地登录，被迫下线
    const  ACCESSTOKEN_LIMIT_ERROR   = 464; //请求频率过高，请稍后重试

    //PERMISSION - (481 -- 500)
    const  PERMISSION_NO_ERROR       = 481; //没有权限执行此操作
    const  ROLE_NOTEXIST_ERROR       = 482; //角色不存在
    const  ROLE_NAME_EXIST_ERROR     = 483; //角色名称已经存在
    const  ROLE_USER_EXIST_ERROR     = 484; //角色下存在用户
    const  ROLE_CANT_DELETE_ERROR    = 485; //系统角色不可删除
    const  ROLE_NEED_ONE_ERROR       = 486; //用户至少保留一个角色
    const  ROLE_CANT_ADD_ERROR       = 487; //系统角色不可添加
    const  USER_ADMIN_CANNOT_OPERATE = 488; //不能修改超级管理员
    const  USER_CANNOT_REGISTER_ROLE = 489; //不能注册此角色的的用户

    //Param - (521 -- 540)
    const  PARAM_ERROR          = 521; //参数错误
    const  VERIFYCODE_ERROR     = 522; //校验码错误
    const  PARAM_SAFEERROR      = 523; //系统检测到有攻击行为存在
    const  VERIFYCODE_EXPIRE    = 525; //校验码过期
    const  QRCODE_EXPIRE        = 526; //二维码过期
    const  QRCODE_NOTBIND_ERROR = 527; //用户未授权绑定
    const  QRCODE_BIND_ERROR    = 528; //用户已绑定
    const  CAPTCHA_ERROR        = 529; //验证码错误
    const  CAPTCHA_EXPIRE       = 530; //验证码过期
    const  RETURN_TYPE_ERROR    = 531; //返回类型有误
    const  SLIDE_NOTEXIST_ERROR = 532; //幻灯片不存在
    const  FILE_COLS_ERROR      = 533; //数据列数不合法

    //Param - (541 -- 560)
    const  MAIL_SEND_ERROR         = 541; //邮件发送失败
    const  MAIL_ADDRESSEE_TOO_MORE = 542; //收件人一次不能超过10个
    const  PHONE_SEND_ERROR        = 543; //手机校验码发送失败

    //上传 - (661 - 680)
    const  UPL_VOID               = 661; //空的上传请求
    const  UPL_NO_FILE_NAME       = 662; //服务器获取的文件名有误
    const  UPL_TMP_PATH           = 663; //临时文件的路径创建失败
    const  UPL_TMPFILE_READ_ERR   = 664; //分片读取失败
    const  UPL_THUNK_GETERR       = 665; //分片上传失败
    const  UPL_TMPFILE_WRITE_ERR  = 666; //分片保存失败
    const  UPL_FILE_CREATE_ERR    = 667; //目标文件创建失败
    const  UPL_THUNK_TO_FILE_ERR  = 668; //分片写入总文件失败
    const  UPL_CHUNK_MISS         = 669; //分片丢失
    const  UPL_EXCEED_NUM_LIMIT   = 670; //上传的文件过多
    const  UPL_INFO_CREATE_ERR    = 671; //文件信息创建失败
    const  UPL_UPLOAD_ERROR       = 672; //文件上传失败
    const  URL_TMP_FILE_NOT_FOUND = 673; //找不到临时文件
    const  URL_TMP_FILE_ERROR     = 674; //临时文件错误

    //Auth - (701 - 720)
    const  AUTH_NOT_EXIST               = 701; //授权信息未找到
    const  AUTH_MACHINESCODE_GET_ERROR  = 702; //机器码读取失败
    const  AUTH_MACHINESCODE_ERROR      = 703; //机器码错误
    const  AUTH_MACHINESCODE_EXIST      = 704; //机器码已经注册
    const  AUTH_ACTIVECODE_ERROR        = 705; //注册码错误
    const  AUTH_ACTIVECODE_NOTEXIST     = 706; //未授权
    const  AUTH_FILE_NOTEXIST_ERROR     = 707; //授权文件未找到
    const  AUTH_FILE_WIRTE_ERROR        = 708; //授权文件写入失败
    const  AUTH_FILE_READ_ERROR         = 709; //授权文件读取失败
    const  AUTH_FILE_ERROR              = 710; //授权文件信息有误
    const  AUTH_EXPIRE_ERROR            = 711; //激活码已过期
    const  AUTH_ACTIVECODE_ACTIVE_ERROR = 712; //激活码已经在该产品上激活
    const  AUTH_API_ACCESSKEY_ERROR     = 713; //API应用接入授权-AccessKey错误
    const  AUTH_API_SECRETKEY_ERROR     = 714; //API应用接入授权-SecretKey错误
    const  AUTH_API_ERROR               = 715; //API应用接入授权格式有误

    //SSO - (721 - 740)
    const  SSO_ATTACH_CHECK_ERROR   = 721; //SSO校验失败
    const  SSO_SESSIONKEY_NOT_ERROR = 722; //Broker didn't send a session key
    const  SSO_ATACHE_NOT_ERROR     = 723; //Broker session id isn't attached to a user session， 403 ||
    // Checksum failed: Client IP address may have changed", 403
    const  SSO_SESSION_EXIST_ERROR = 724; //Session has already started
    const  SSO_SESSIONID_INVALID   = 725; //Invalid session id

    //文件 - (761 - 780)
    const  FILE_EXIST_ERROR                = 761; //文件已存在
    const  FILE_NOTEXIST_ERROR             = 762; //文件不存在
    const  FILE_DERECTORY_NAME_ERROR       = 763; //文件/文件夹名不能包含以下字符/\:*?|<>
    const  FILE_FRAME_ERROR                = 764; //文件截图失败
    const  FILE_BROWSE_IMG_SIZE_ERROR      = 765; //图片大小过大，不支持在线浏览
    const  FILE_TASK_STATUE_ERROR          = 766; //转码失败
    const  FILE_TASK_STATUE_RUNNING_ERROR  = 767; //正在转码
    const  FILE_TASK_CVT_NOTEXIST_ERROR    = 768; //转码后的文件已丢失
    const  FILE_DOWNLOAD_SIZE_ERROR        = 769; //文件打包下载错误
    const  FILE_DOWNLOAD_SINGLE_SIZE_ERROR = 770; //单文件不得超过100M
    const  FILE_NOTIMAGE_ERROR             = 771; //文件类型不是图片
    const  FILE_NOTEXIST_IMAGE_ERROR       = 772; //文件不存在或不是图片
    const  FILE_NOTEXIST_PERMISSION_ERROR  = 773; //文件不存在或无读取权限
    const  FILE_IMAGE_LOAD_ERROR           = 774; //图片文件加载失败

    //错误信息
    protected static $_msg = [
        self:: SUCCESS   => '操作成功',
        self:: ERROR_404 => '跳转404页面',
        self:: API_ERR   => '程序中断',

        self:: URL_ERROR                      => 'url有误',
        self:: URL_EXPIRED_ERROR              => 'url失效',
        self:: URL_ACCESSTOKEN_NOTEXIST_ERROR => 'url缺少授权信息ACCESS-TOKEN',

        self:: DB_CONNECT_ERROR   => '数据库连接失败',
        self:: DB_OPERATION_ERROR => '数据操作失败',
        self:: DB_ADD_ERR         => '添加失败',

        self:: USER_PASSWD_ERROR          => '密码错误',
        self:: USER_NOTLOGIN_ERROR        => '用户未登陆',
        self:: USER_NOTACTIVE_ERROR       => '用户未激活',
        self:: USER_NOPERMISSION_ERROR    => '用户未授权',
        self:: USER_NOTEXIST_ERROR        => '用户不存在',
        self:: USER_EXIST_ERROR           => '用户已经存在',
        self:: USER_PASSWD_NULL_ERROR     => '密码为空',
        self:: USER_EMAIL_EXIST_ERROR     => '该邮箱已被注册，请重新输入',
        self:: USER_OLD_PWD               => '新旧密码相同',
        self:: USER_OLD_PWD_ERR           => '原密码不正确',
        self:: USER_NEW_PWD_NULL          => '新密码不能为空',
        self:: USER_PASSWD_SAME_ERROR     => '密码不能和用户名相同',

        self:: USER_PHONE_NOTEXIST_ERROR  => '手机号不存在',
        self:: USER_PHONE_EXIST_ERROR     => '手机号已经存在',

        self:: USER_ACTIVE_ERROR          => '用户已激活',
        self:: USER_DISABLE_ERROR         => '用户已禁用，请联系管理员',
        self:: USER_EMAIL_NOT_EXIST_ERROR => '邮箱不存在',

        self:: ACCESSTOKEN_ERROR         => '帐号令牌授权无效',
        self:: ACCESSTOKEN_EXPIRED_ERROR => '帐号登录已过期',
        self:: ACCESSTOKEN_OFFLINE_ERROR => '帐号异地登录，被迫下线',
        self:: ACCESSTOKEN_LIMIT_ERROR   => '请求频率过高，请稍后重试',

        self:: PERMISSION_NO_ERROR    => '没有权限执行此操作',
        self:: ROLE_NOTEXIST_ERROR    => '此角色已删除',
        self:: ROLE_NAME_EXIST_ERROR  => '此角色已存在',
        self:: ROLE_USER_EXIST_ERROR  => '此角色下有用户，无法删除',
        self:: ROLE_CANT_DELETE_ERROR => '系统角色不可删除',
        self:: ROLE_NEED_ONE_ERROR    => '用户至少保留一个角色',
        self:: ROLE_CANT_ADD_ERROR    => '系统角色不可添加',
        self:: USER_ADMIN_DELETE_ERROR    => '超级管理员不能删除',
        self:: USER_ADMIN_CANNOT_OPERATE  => '不能对超级管理员进行操作',
        self:: USER_CANNOT_REGISTER_ROLE  => '不能注册此角色的用户',

        self:: PARAM_ERROR          => '参数错误',
        self:: VERIFYCODE_ERROR     => '校验码错误',
        self:: PARAM_SAFEERROR      => '系统检测到有攻击行为存在',
        self:: VERIFYCODE_EXPIRE    => '校验码过期',
        self:: QRCODE_EXPIRE        => '二维码过期',
        self:: QRCODE_NOTBIND_ERROR => '用户未授权绑定',
        self:: QRCODE_BIND_ERROR    => '账号已被绑定',
        self:: CAPTCHA_ERROR        => '验证码错误',
        self:: CAPTCHA_EXPIRE       => '验证码过期',
        self:: RETURN_TYPE_ERROR    => '返回类型有误',
        self:: SLIDE_NOTEXIST_ERROR => '幻灯片不存在',
        self:: FILE_COLS_ERROR      => '数据列数不合法',

        self:: MAIL_SEND_ERROR         => '邮件发送失败',
        self:: MAIL_ADDRESSEE_TOO_MORE => '收件人一次不能超过10个',
        self:: PHONE_SEND_ERROR        => '手机校验码发送失败',

        self:: UPL_VOID               => '空的上传请求',
        self:: UPL_NO_FILE_NAME       => '服务器获取的文件名有误',
        self:: UPL_TMP_PATH           => '临时文件的路径创建失败',
        self:: UPL_TMPFILE_READ_ERR   => '分片读取失败',
        self:: UPL_THUNK_GETERR       => '分片获取失败',
        self:: UPL_TMPFILE_WRITE_ERR  => '分片保存失败',
        self:: UPL_FILE_CREATE_ERR    => '目标文件创建失败',
        self:: UPL_THUNK_TO_FILE_ERR  => '分片写入总文件失败',
        self:: UPL_CHUNK_MISS         => '分片丢失',
        self:: UPL_EXCEED_NUM_LIMIT   => '上传的文件过多',
        self:: UPL_INFO_CREATE_ERR    => '文件信息创建失败',
        self:: UPL_UPLOAD_ERROR       => '文件上传失败',
        self:: URL_TMP_FILE_NOT_FOUND => '文件可能被移动或删除，请重新上传',
        self:: URL_TMP_FILE_ERROR     => '临时文件错误',


        self:: AUTH_NOT_EXIST               => '授权信息未找到',
        self:: AUTH_MACHINESCODE_GET_ERROR  => '机器码读取失败',
        self:: AUTH_MACHINESCODE_ERROR      => '机器码错误',
        self:: AUTH_MACHINESCODE_EXIST      => '机器码已经注册',
        self:: AUTH_ACTIVECODE_ERROR        => '注册码错误',
        self:: AUTH_ACTIVECODE_NOTEXIST     => '未授权',
        self:: AUTH_FILE_NOTEXIST_ERROR     => '授权文件未找到',
        self:: AUTH_FILE_WIRTE_ERROR        => '授权文件写入失败',
        self:: AUTH_FILE_READ_ERROR         => '授权文件读取失败',
        self:: AUTH_FILE_ERROR              => '授权文件信息有误',
        self:: AUTH_EXPIRE_ERROR            => '激活码已过期',
        self:: AUTH_ACTIVECODE_ACTIVE_ERROR => '激活码已经在该产品上激活',
        self:: AUTH_API_ACCESSKEY_ERROR     => 'API应用接入授权-AccessKey错误',
        self:: AUTH_API_SECRETKEY_ERROR     => 'API应用接入授权-SecretKey错误',
        self:: AUTH_API_ERROR               => 'API应用接入授权格式有误',

        self:: SSO_ATTACH_CHECK_ERROR   => 'SSO 校验失败',
        self:: SSO_SESSIONKEY_NOT_ERROR => 'Broker didn\'t send a session key',
        self:: SSO_ATACHE_NOT_ERROR     => 'Broker session id isn\'t attached to a user sso session， 403',
        self:: SSO_SESSION_EXIST_ERROR  => 'Session has already started',
        self:: SSO_SESSIONID_INVALID    => 'Invalid session id',

        self:: FILE_EXIST_ERROR                => '文件已存在',
        self:: FILE_NOTEXIST_ERROR             => '文件不存在',
        self:: FILE_DERECTORY_NAME_ERROR       => '文件/文件夹名不能包含以下字符/\:*?|<>"',
        self:: FILE_BROWSE_IMG_SIZE_ERROR      => '图片大小过大，不支持在线浏览',
        self:: FILE_FRAME_ERROR                => '文件截图失败',
        self:: FILE_TASK_STATUE_ERROR          => '转码失败',
        self:: FILE_TASK_STATUE_RUNNING_ERROR  => '正在转码',
        self:: FILE_TASK_CVT_NOTEXIST_ERROR    => '转码后的文件已丢失',
        self:: FILE_DOWNLOAD_SIZE_ERROR        => '文件总大小不得超过2G',
        self:: FILE_DOWNLOAD_SINGLE_SIZE_ERROR => '单文件不得超过100M',
        self:: FILE_NOTIMAGE_ERROR             => '文件类型不是图片',
        self:: FILE_NOTEXIST_IMAGE_ERROR       => '文件不存在或不是图片',
        self:: FILE_NOTEXIST_PERMISSION_ERROR  => '文件不存在或无读取权限',
        self:: FILE_IMAGE_LOAD_ERROR           => '图片文件加载失败',
    ];

    /**
     * 根据错误码获取错误信息
     *
     * @param $code
     *
     * @return mixed
     * @throws Exception
     * @author jiangjiaxiong
     *
     */
    public static function GetMsg($code)
    {
        if (!is_int($code))
        {
            throw new \Exception('错误码只能是整数');
        }

        if (!isset(self::$_msg[$code]))
        {
            return 'API出现致命的非预期错误';
        }

        return self::$_msg[$code];
    }

    /**
     * GetClassConstants
     *
     * @return array
     * @author jiangjiaxiong
     * @date
     */
    public function GetClassConstants()
    {
        $reflect = new ReflectionClass(EC::class);

        return $reflect->getConstants();
    }

    public static function Has($code)
    {
        if (!isset(self::$_msg[$code]))
        {
            return false;
        }

        return true;
    }

    public static function GetCode()
    {
        return self::$_msg;
    }
}
