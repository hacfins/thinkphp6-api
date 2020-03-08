<?php

namespace app\api\controller;

use app\api\logic\BaseLogic;
use app\api\controller\traits\
{DataCheck, DataResponse, IReflectionDef, Reflection};
use app\common\facade\Request;
use think\facade\
{View};
use think\App;

/**
 * 服务层基类
 */
class BaseController implements IReflectionDef
{
    use DataCheck;
    use DataResponse;
    use Reflection;

    /**
     * Request实例
     *
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     *
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     *
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     */
    protected $middleware = [
        \app\common\middleware\CBegin::class,
        \app\common\middleware\CResource::class => ['except' => ['index', 'home_index']],
        \app\common\middleware\CSSO::class      => ['except' => ['index', 'home_index']],
        \app\common\middleware\CAPI::class      => ['except' => ['index', 'home_index']],
        \app\common\middleware\CProduct::class  => ['except' => ['index', 'home_index']],
    ];

    protected static $_input = false; //请求参数

    public static $_uname = false; //用户名
    public static $_token = false; //用户令牌

    /**
     * 构造方法
     *
     * @access public
     *
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    /**
     * 构造器
     *
     * @return array | mixed
     */
    protected function initialize()
    {
    }

    /**
     * 操作方法容错
     *
     * @author jiangjiaxiong
     * @date
     *
     * @param Request $request
     *
     * @return
     */
    public function _empty(Request $request)
    {
        return $this->R(\EC::URL_ERROR, strtolower(' ' . $request->domain() . '/' . MODULE_NAME . '/' .
            $request->controller() . '/' . $request->action() . ' 不存在'), '请求地址错误');
    }

    /**
     * API 首页
     */
    public function home_index()
    {
        return View::fetch('index/index');
    }

    // +--------------------------------------------------------------------------
    // |  用户信息 - General
    // +--------------------------------------------------------------------------
    /**
     * 身份识别 - 必有有令牌
     *
     * @author jiangjiaxiong
     * @date
     */
    protected function NeedToken()
    {
        if (!self::$_token)
        {
            BaseLogic::$_error_code = \EC::URL_ACCESSTOKEN_NOTEXIST_ERROR;
            return false;
        }

        return true;
    }

    /**
     * 身份识别 - 是否是超级管理员
     *
     * @author jiangjiaxiong
     * @date
     * @return bool
     */
    protected function IsAdmin()
    {
        if (self::$_uname == USER_NAME_ADMIN)
        {
            return true;
        }

        return false;
    }
}