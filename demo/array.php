<?php
/**
 * Created by IntelliJ IDEA.
 * User: hk
 * Date: 2018/11/16
 * Time: 17:16
 */

//extra - 从数组中将变量导入到当前的符号表
$size = 'large';
$var_arr = [
    'color' => 'blue',
    'size'  => 'medium',
    'shape' => 'sphere'
];

extract($var_arr, EXTR_PREFIX_ALL, 'ar');
echo "$ar_color, $ar_size, $ar_shape" . "<br>";

//compact - 建立一个数组，包括变量名和它们的值
//在 PHP 7.3 之前版本，未设置的字符串会被静默忽略
$result = compact(['ar_color', 'ar_size', 'ar_shape']);
var_dump($result);