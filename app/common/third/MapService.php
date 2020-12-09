<?php
namespace app\common\third;

/*
 * 高德/百度等-API
 */
class MapService
{
    /*
     * 地理/逆地理编码, http://lbs.amap.com/api/webservice/reference/georegeo
     *
     * 采用济南分公司名义注册的key。日调用超量：400万次，1分钟调用超量：6万。
     *
     * Todo：后期需要考虑使用买家的key!!!
     *
     */
    private static $m_restapi = 'http://restapi.amap.com/v3/geocode/regeo?' . 'output=json' . '&key=d327e8a6854d0bdfeda518f023fe9810';

    /*
     * IP定位API, http://api.map.baidu.com/location/ip
     *
     * 每个key每天支持100万次调用，超过限制不返回数据。
     *
     * Todo：后期需要考虑使用买家的key!!!
     */
    private static $m_apimap = 'http://api.map.baidu.com/location/ip?' . 'ak=CiqSdD1GVtCTdflz5pfpd1eg' . '&coor=bd09ll';

    /*
     * 根据经纬度坐标获取行政区号-&location=116.310003,39.991957
     */
    public static function Get_Adcode($lantitude, $longitude, & $adcode)
    {
        if(yaconf('switch.offline'))
        {
            return false;
        }

        try
        {
            $requestAPi = self::$m_restapi . '&location=' . $longitude . ',' . $lantitude;

            $opts    = array(
                'http' => array(
                    'method'  => 'GET',
                    'timeout' => 1,//单位秒
                )
            );
            $content = file_get_contents($requestAPi, false, stream_context_create($opts));
            $jsonArr = json_decode($content, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS);

            // 说明断网
            if (!isset($jsonArr) || is_null($jsonArr) || !isset($jsonArr['status']))
            {
                return false;
            }

            // 说明获取失败
            if ($jsonArr['status'] != '1' && $jsonArr['info'] != 'OK')
            {
                return false;
            }

            $adcode = $jsonArr['regeocode']['addressComponent']['adcode']; //北京市朝阳区110105
        }
        catch (\Throwable $e)
        {
            \think\facade\Log::error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 根据Ip获取经纬度坐标
     */
    public static function Get_Location($internetIp, & $lantitude, & $longitude)
    {
        if(yaconf('switch.offline'))
        {
            return false;
        }

        try
        {
            $requestAPi = self::$m_apimap;
            if (isset($internetIp))
            {
                //内网IP
                //  A类10.0.0.0～10.255.255.255
                //  B类172.16.0.0～172.31.255.255
                //  C类192.168.0.0～192.168.255.255
                //  ......
                $bLocalIp = !filter_var($internetIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
                if(!$bLocalIp)
                    $requestAPi .= "&ip=$internetIp";
            }

            $opts = array(
                'http' => array(
                    'method'  => 'GET',
                    'timeout' => 1,//单位秒
                )
            );

            $content = file_get_contents($requestAPi, false, stream_context_create($opts));
            $jsonArr = json_decode($content, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS);

            // 说明断网
            if (!isset($jsonArr) || is_null($jsonArr) || !isset($jsonArr['status']))
            {
                return false;
            }

            // 说明获取失败
            if ($jsonArr['status'] != '0')
            {
                return false;
            }

            $lantitude = $jsonArr['content']['point']['y'];
            $longitude = $jsonArr['content']['point']['x'];
        }
        catch (\Throwable $e)
        {
            \think\facade\Log::error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 根据Ip获取地址位置
     */
    public static function GetIpInfo($internetIp = '')
    {
        if(yaconf('switch.offline'))
        {
            return [];
        }

        try
        {
            //内网IP
            //  A类10.0.0.0～10.255.255.255
            //  B类172.16.0.0～172.31.255.255
            //  C类192.168.0.0～192.168.255.255
            //  ......
            $bLocalIp = !filter_var($internetIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
            if($bLocalIp)
                $internetIp = 'myip';

            //$requestAPi = "http://ip.taobao.com/service/getIpInfo.php?ip=" . $internetIp;
            $requestAPi = "http://ip.taobao.com/outGetIpInfo?accessKey=alibaba-inc&ip=" . $internetIp;
            $opts       = array(
                'http' => array(
                    'method'  => 'GET',
                    'timeout' => 1, // 单位秒
                )
            );
            $jsonArr = json_decode( file_get_contents($requestAPi, false, stream_context_create($opts)),
                JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS );

            // 说明断网
            if (!isset($jsonArr) || !isset($jsonArr['code']))
            {
                return [];
            }

            // 0 表示成功
            if ($jsonArr['code'] !== 0)
            {
                return [];
            }

            //  "ip": "223.98.166.115",
            //  "country": "中国",
            //  "area": "",
            //  "region": "山东",
            //  "city": "济南",
            //  "county": "XX",
            //  "isp": "移动",
            //  "country_id": "CN",
            //  "area_id": "",
            //  "region_id": "370000",
            //  "city_id": "370100",
            //  "county_id": "xx",
            //  "isp_id": "100025"
            $data = (array)$jsonArr['data'];

            return $data;
        }
        catch (\Throwable $e)
        {
            \think\facade\Log::error($e->getMessage());
            return [];
        }
    }
}