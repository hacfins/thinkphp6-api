<?php

use think\facade\Route;

//output temp image
Route::rule('tmp_avatar/:year', 'base/tmp_avatar');
Route::rule('tmp_imgs/:year', 'base/tmp_imgs');

//将多级分层的 "." 改为 "/"
Route::rule('passport/:control/:action', 'passport.:control/:action'); //-登录、注册、找回密码等
Route::rule('product/:control/:action', 'product.:control/:action');   //-产品授权等
Route::rule('website/:control/:action', 'website.:control/:action');   //-配置信息