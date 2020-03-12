<?php
namespace app\common\validate;

use think\Validate;

class ValidateEx extends Validate
{
    // 验证规则默认提示信息
    protected static $typeMsgEx = [
        'alphaPrefix' => ':attribute只能以字母开头',
        'alphaDash2'  => ':attribute只能是字母、数字、符号，至少有两种',
    ];

    /**
     * 构造函数
     * @access public
     * @param array $rules 验证规则
     * @param array $message 验证提示信息
     * @param array $field 验证字段描述信息
     */
    public function __construct()
    {
        parent::__construct();
        parent::setTypeMsg(self::$typeMsgEx);
    }

    /**
     * 以字母开头
     * @param mixed     $value  字段值
     * @param mixed     $rules  验证规则
     * @param array     $data   数据
     * @param string    $title  字段描述
     * @param array     $msg    提示信息
     *
     * @return bool
     */
    protected function alphaPrefix($value, $rules, $data, $title = '', $msg = [])
    {
        if (!preg_match('/^([a-zA-Z]{1}.*)$/', $value))
        {
            return false;
        }

        return true;
    }

    /**
     * 字母、数字、符号，至少有两种
     * @return bool
     */
    protected function alphaDash2($value, $rules, $data, $title = '', $msg = [])
    {
        if (!preg_match('/^(?![\d]+$)(?![a-zA-Z]+$)(?![^\da-zA-Z]+$).*$/', $value))
        {
            return false;
        }

        return true;
    }
}