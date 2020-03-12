<?php
namespace app\common\facade;

use think\facade;

/**
 * @see \Sinergi\BrowserDetector\Device
 * @mixin \Sinergi\BrowserDetector\Device
 * @method mixed setUserAgent(UserAgent $userAgent) static
 * @method mixed getUserAgent() static
 * @method string getName() static
 * @method mixed setName($name) static
 *
 */
class Device extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Sinergi\BrowserDetector\Device';
    }
}