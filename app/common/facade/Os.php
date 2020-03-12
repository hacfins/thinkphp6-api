<?php
namespace app\common\facade;

use think\facade;

/**
 * @see \Sinergi\BrowserDetector\Os
 * @mixin \Sinergi\BrowserDetector\Os
 * @method string getName() static Return the name of the OS.
 * @method mixed setName($name) static Set the name of the OS.
 * @method string getVersion() static Return the version of the OS.
 * @method mixed setVersion($version) static Set the version of the OS.
 * @method bool getIsMobile() static Is the browser from a mobile device?
 * @method bool isMobile() static
 * @method bool setIsMobile($isMobile = true) static Set the Browser to be mobile.
 * @method mixed setUserAgent(UserAgent $userAgent) static
 * @method getUserAgent() static
 *
 */
class Os extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Sinergi\BrowserDetector\Os';
    }
}