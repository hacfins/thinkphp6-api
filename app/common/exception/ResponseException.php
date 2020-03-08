<?php
namespace app\common\exception;

/**
 * 自定义异常
 * 用于处理即时返回数据、不显示Trace信息的场景
 */
class ResponseException extends \think\Exception
{

}