<?php

namespace string;

/**
 * ID前缀格式化类
 * Func
 * public getPrefixId    生成已加前缀的id
 * public getId          还原为id
 * public getPrefixType  根据已加前缀id获取前缀类型
 */
class Prefix
{ // class start

    // 定义前缀常量
    const USER_TYPE    = 'user';    // 用户
    const ORDER_TYPE   = 'order';   // 订单
    const MESSAGE_TYPE = 'message'; // 消息

    // 前缀设定
    private static $prefix = array(
        self::USER_TYPE    => 'U',
        self::ORDER_TYPE   => 'O',
        self::MESSAGE_TYPE => 'M'
    );

    /**
     * 创建带前缀的id
     *
     * @param  Int $id          id
     * @param  Int $prefix_type 类型
     *
     * @return String
     */
    public static function getPrefixId($id, $prefix_type = '')
    {
        // 有自定义前缀类型
        if (isset(self::$prefix[$prefix_type]))
        {
            return self::$prefix[$prefix_type] . $id;
        }

        // 没有自定义前缀类型
        return $id;
    }

    /**
     * 还原为id
     *
     * @param  String $prefix_id 已加前缀id
     *
     * @return Int
     */
    public static function getId($prefix_id)
    {
        preg_match('/\d+/', $prefix_id, $arr);
        if (isset($arr[0]))
        {
            return $arr[0];
        }

        return 0;
    }

    /**
     * 根据已加前缀id获取前缀类型
     *
     * @param  String $prefix_id 已加前缀id
     *
     * @return Int
     */
    public static function getPrefixType($prefix_id)
    {

        // 获取id前缀
        preg_match('/[A-Za-z]+/', $prefix_id, $arr);

        if (isset($arr[0]))
        {
            $prefix = $arr[0];

            // 获取前缀
            $prefixs = array_flip(self::$prefix);
            if (isset($prefixs[$prefix]))
            {
                return $prefixs[$prefix];
            }
        }

        return '';
    }

} // class end