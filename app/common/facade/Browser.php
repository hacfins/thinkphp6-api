<?php
namespace app\common\facade;

use think\facade;

/**
 * @see \Sinergi\BrowserDetector\Browser
 * @mixin \Sinergi\BrowserDetector\Browser
 * @method mixed setName($name) static Set the name of the OS.
 * @method string getName() static Return the name of the Browser.
 * @method bool isBrowser($name) static Check to see if the specific browser is valid.
 * @method mixed setVersion($version) static Set the version of the browser.
 * @method string getVersion() static The version of the browser.
 * @method mixed setIsRobot($isRobot) static Set the Browser to be a robot.
 * @method bool getIsRobot() static Is the browser from a robot (ex Slurp,GoogleBot)?
 * @method bool isRobot() static
 * @method mixed setIsChromeFrame($isChromeFrame) static
 * @method mixed getIsChromeFrame() static Used to determine if the browser is actually "chromeframe".
 * @method bool isChromeFrame() static
 * @method mixed setIsFacebookWebView($isFacebookWebView) static
 * @method bool getIsFacebookWebView() static Used to determine if the browser is actually "facebook".
 * @method bool isFacebookWebView() static
 * @method mixed setUserAgent(UserAgent $userAgent) static
 * @method mixed  getUserAgent() static
 * @method mixed setIsCompatibilityMode($isCompatibilityMode) static
 * @method bool isCompatibilityMode() static
 * @method endCompatibilityMode() static Render pages outside of IE's compatibility mode.
 *
 */
class Browser extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Sinergi\BrowserDetector\Browser';
    }
}

