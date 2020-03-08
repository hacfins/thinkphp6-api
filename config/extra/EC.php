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

    //FILE - (501 -- 520)
    const  SOURCE_NOT_EXIST_ERROR     = 501; //资源或路径不存在
    const  FILE_BROWSE_IMG_SIZE_ERROR = 502; //图片尺寸过大，不支持在线浏览
    const  FILE_COPY_ERROR            = 504; //文件复制失败
    const  CONFIG_GET_ERROR           = 510; //网站设置获取失败
    const  CONFIG_SMTP_ERROR          = 511; //邮件发送配置信息获取失败
    const  DIR_MK_ERR                 = 517; //文件夹创建失败
    const  FILE_TYPE_NOTESUPPORT      = 518; //文件类型不支持
    const  FILE_FRAME_ERROR           = 519; //文件截图失败

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
        self:: DB_RECORD_NOTEXIST => '数据记录不存在',

        self:: SOURCE_NOT_EXIST_ERROR     => '资源不存在',
        self:: FILE_BROWSE_IMG_SIZE_ERROR => '图片尺寸过大，不支持在线浏览',
        self:: FILE_COPY_ERROR            => '文件复制失败',
        self:: CONFIG_GET_ERROR           => '网站设置获取失败',
        self:: CONFIG_SMTP_ERROR          => '邮件发送配置信息获取失败',
        self:: DIR_MK_ERR                 => '文件夹创建失败',
        self:: FILE_TYPE_NOTESUPPORT      => '文件类型不支持',
        self:: FILE_FRAME_ERROR           => '文件截图失败',

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
