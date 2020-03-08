# thinkphp6-api

> 运行环境要求 PHP7.1+

##1. 简介

thinkphp6-api 可用于后台单应用及多应用的 API 接口开发，它基于 [thinkphp6](https://github.com/top-think/think)实现。它使用了最新的后台技术栈，相信不管你的需求是什么，本项目都能帮助到你。

##2. 功能

##3. 编码命名规范

严格按照 ThinkPHP 6.0 的编码规范，但有以下不同：
- 类 class 中的数据成员变量采用 `_` 前缀的小驼峰法，如 `$_userName;` 
- 类 class 中的函数成员变量采用大驼峰法，如`public function GetUser()`
- 添加、删除、修改、上传、保存、是否存在、信息、列表 对应的函数名称前缀为
 `Add`、`Del`、`Modify`、`Upload`、`Save`、`Exist`、`Info`、`GetList`
- API 提供的接口，采用大驼峰法 + _ 进行分割
 `Add`、`Del`、`Modify`、`Upload`、`Save`、`Exist`、`Info`、`Get_List`

> TP自身的数据成员变量,沿用其本身的规范

##4. 注意事项
 
 - 使用 `\EC` 组织错误码和错误信息
 - api 地址不正确，会给出友好的提示
 - 为了避免使用 `use app\api\model\User as UserM;`, 开启了控制器的后缀名(Controller)
 - 在控制器层和业务逻辑层分别有获取当前登录用户信息以及身份的方法，不需要额外查表
 
 ```php
  控制器，self::$_uid、self::$_token
  模型，$this->GetUserName()
 ```
 
 > 业务的错误使用返回值直接返回，无法处理的请求采用抛异常 — E() 函数，无需显示异常trace详情的抛出异常需要传入false参数
 这样的设计保证了调用者无论如何都有足够的控制权，从而让整个系统是健壮的。

###4.1 服务层

服务层是 API 调用的入口，统一处理 API 应用接入授权、产品注册授权检测、API 权限控制等

- 使用 Base 控制器的 `I()` 方法接收并验证请求参数
- 调用业务逻辑层, 使用 Base 控制器的 `return $this->R()` 方法返回数据
- 一个函数就是一个独立的 API 接口
- 服务层之间不可相互调用

###4.2 业务逻辑层

- 调用模型层，完成业务的逻辑处理
- 一个函数完成一个独立的业务逻辑处理
- 内部相关权限的授权检测
- 不直接参与数据库的操作
- 独立的业务逻辑层之间不可相互调用
- 通过全局的`$_error_code`、`$_error_msg`返回错误信息

###4.3 模型层

- 调用数据库，完成数据的读写操作
- 单条信息有查询缓存，并且在更新、删除操作时同步缓存信息
- 不进行权限的授权检测（统一由业务逻辑层处理）
- 数据库操作的事务处理交由业务逻辑层处理
- 无法通过返回值处理的可直接抛出异常（尽量避免），不可捕获异常
- 独立的模型层之间不可相互调用

> 新增数据的最佳实践原则：使用 `create` 方法新增数据，使用 `saveAll` 批量新增数据

> 更新的最佳实践原则是：如果需要使用模型事件，那么就先查询后更新，如果不需要使用事件或者不查询直接更新，直接使用静态的`Update`方法进行条件更新。静态`Update`方法只适合单条更新
> 如非必要，尽量不要使用批量更新（或者使用 `->where()->update()` 方式）。

##5. 源码修改说明
所有涉及 Vendor 源码库的修改，需要增加 `//Todo: Hacfin` 标识注释

###5.1 Captioning
修改了 vendor/captioning/captioning/src/Captioning/Cue.php
```php
//Todo: Hacfin
//"\r\t\n\0\x0B"
$this->textLines = array_map('trim', preg_split('/$\R?^/m', $_text), ["\r\t\n\0\x0B"]);
```

###5.2 php-ffmpeg 
修改了 vendor/php-ffmpeg/php-ffmpeg/src/FFMpeg/Media/Frame.php
```php
//Todo: Hacfin
//修改 -ss 时间戳，生成缩略图失败的bug
$timeCode =  (string) $this->timecode;
if('00:00:00.00' == $timeCode)
{
    $keys = array_merge(array_keys($commands, $timeCode, true), array_keys($commands, '-ss', true));
    foreach ($keys as $key)
    {
        unset($commands[$key]);
    }
}
```

###5.3 think-orm
修改了 vendor/topthink/think-orm/src/db/concern/JoinAndViewQuery.php
```php
//新增joins方法
public function joins(array $joins)
    {
        // 如果为组数，则循环调用join
        foreach ($joins as $key => $value) {
            if (is_array($value) && 2 <= count($value)) {
                $this->join($value[0], $value[1], isset($value[2]) ? $value[2] : 'INNER');
            }
        }

        return $this;
    }
```

修改了 vendor/topthink/think-orm/src/db/PDOConnection.php
```php
//Todo: Hacfin
 //修复在高并发下，返回值为null的bug
 $result = $this->cache->get($key);

 if (false !== $result) {
     return $result;
 }
```

###5.4 framework
####5.4.1 SessionInit
修改了 vendor/topthink/framework/src/think/middleware/SessionInit.php
```php
//Todo: Hacfin
//修复 session 数据保存时，无法动态修改过期时间的问题
$this->app->cookie->set($cookieName, $this->session->getId(), $this->app->config->get('cookie.expire'));
```

## thinkphp 6 主要新特性

* 采用`PHP7`强类型（严格模式）
* 支持更多的`PSR`规范
* 原生多应用支持
* 更强大和易用的查询
* 全新的事件系统
* 模型事件和数据库事件统一纳入事件系统
* 模板引擎分离出核心
* 内部功能中间件化
* SESSION/Cookie机制改进
* 对Swoole以及协程支持改进
* 对IDE更加友好
* 统一和精简大量用法

## 文档

[完全开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)

## License

copyright © 2020 北京华科飞扬科技股份公司