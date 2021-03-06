<?php
namespace app\api\controller\traits;

/**
 * 服务层接口反射的 const 定义
 */
interface IReflectionDef
{
    //待反射的模块名
    const MODULE_NAMES = [
        'api'
    ];

    //待反射的模块名
    const MODULES = [
        'api' => [
            //-auth
            'auth'    => [
                'Role',
                'Auth',
                'UserRole',
            ],
            //-location
            'location' => [
                'Area',
            ],
            //-passport
            'passport'  => [
                'Common',
                'User',
                'Wxlogin',
            ],
            //-user
            'user'    => [
                'User',
                'UserLog',
                'UserManage'
            ],
        ]
    ];

    //不反射的控制器名(不区分模块!)
    const HIDDEN_CONTROLLER = [
        'Error',
        'Base',
    ];

    //不需要验证的api
    const WHILE_LIST = [
        //=======================================================================================//
        //========================================= product =====================================//
        //=======================================================================================//
        //----------------------------------------auth-------------------------------------------//
        'api-product.auth.version',
        'api-product.auth.info',
        'api-product.auth.active',

        //=======================================================================================//
        //========================================= passport =====================================//
        //=======================================================================================//
        //----------------------------------------wxlogin-------------------------------------------//
        'api-passport.wxlogin.jssdksign',
        'api-passport.wxlogin.baseinfo',
        'api-passport.wxlogin.userinfo',
        'api-passport.user.login_verify'
    ];
}