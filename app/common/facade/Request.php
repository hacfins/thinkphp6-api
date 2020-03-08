<?php
namespace app\common\facade;

/**
 * 应用请求对象类
 */
class Request extends \think\Request
{
    protected $filter = ['trim'];
}