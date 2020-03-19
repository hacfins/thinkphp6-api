<?php
namespace app\api\controller\location;

use app\api\controller\BaseController;
use app\api\logic\
{
    LocationLogic
};

/**
 * 省市区
 */
class AreaController extends BaseController
{
    public function Index()
    {
        return $this->R();
    }

    /**
     * 获取所有的市列表
     */
    public function GetCityListAll()
    {
        $provicesList = (new LocationLogic())->GetCityListAll();

        if ($provicesList)
            return $this->R(null, null, $provicesList);

        return $this->R();
    }

    /**
     * 获取区列表
     */
    public function GetAreaList()
    {
        //**数据接收**
        $vali = $this->I([
            [
                'city_code',
                null,
                'd',
                'require|>:0'
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $areasList = (new LocationLogic())->GetAreaList(self::$_input['city_code']);

        //**数据返回**
        if ($areasList)
            return $this->R(null, null, $areasList);

        return $this->R();
    }
}