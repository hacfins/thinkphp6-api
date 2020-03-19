<?php
namespace app\api\logic;

use app\api\model\
{
    location\AreaArea, location\AreaCity, location\AreaProvince
};

/**
 * 省市区
 */
class LocationLogic extends BaseLogic
{
    /**
     * 获取所有的市列表
     *
     * @return array|bool
     */
    public function GetCityListAll()
    {
        try
        {
            $provicesList = AreaProvince::instance()->GetList();
            $cityCls      = AreaCity::instance();

            // 根据省获取市列表
            foreach ($provicesList as &$provice)
            {
                $provice['citys'] = $cityCls->GetList($provice['code']);
            }

            return $provicesList;
        }
        catch(\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取区列表
     *
     * @return array|bool
     */
    public function GetAreaList(int $cityCode)
    {
        try
        {
            $cityList = AreaArea::instance()->GetList($cityCode);

            return $cityList;
        }
        catch(\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();
            return false;
        }
    }
}