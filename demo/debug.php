<?php

function a_test($str)
{
    echo "\nHi: $str";

    //堆栈回溯
    var_dump(debug_backtrace());
}
a_test('friend');


function fact(int $n)
{
    echo "<br>";
    //看程序的调用栈（常用于递归、函数嵌套的查看）
    debug_print_backtrace();

    if($n < 1)
        return 0;
    else if($n == 1)
        return $n;

    return $n * fact($n-1);
}

debug_zval_dump(fact(3));

