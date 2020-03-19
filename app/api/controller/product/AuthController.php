<?php
namespace app\api\controller\product;

use app\api\controller\BaseController;
use app\api\logic\
{
    ProductLogic
};

/**
 * 产品授权
 */
class AuthController extends BaseController
{
    public function Index()
    {
        return $this->R();
    }

    /*
    * 版本
    */
    public function Version()
    {
        return $this->R(null, null, [
            'name'    => AUTH_PRODUCT_NAME,
            'version' => AUTH_PRODUCT_VERSION
        ]);
    }

    /**
     * 获取产品授权信息
     */
    public function Info()
    {
        $licenseArr = (new ProductLogic())->ReadFile();

        //**数据返回**
        if ($licenseArr)
            return $this->R(null, null, $licenseArr,null,null, time());

        return $this->R();
    }

    /**
     * 激活
     */
    public function Active()
    {
        //**数据接收**
        $vali = $this->I([
            [
                'code',
                null,
                's',
                'require|length:4,256'
            ],
            [
                'register',
                null,
                's',
                'require|length:4,32'
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        (new ProductLogic())::Active(self::$_input['code'], self::$_input['register']);

        //**数据返回**
        return $this->R();
    }
}