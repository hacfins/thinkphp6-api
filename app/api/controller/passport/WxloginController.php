<?php

namespace app\api\controller\passport;

use app\api\controller\BaseController;
use app\api\logic\
{UserLoginLogic, WxLoginLogic};

/**
 * 微信登录、绑定
 */
class WxloginController extends BaseController
{
    public function Index()
    {
        return $this->R();
    }

    //==================================================== JS-SDK ======================================================
    /**
     * JS-SDK sign 签名信息
     */
    public function JsSDKSign()
    {
        //数据接收
        $vali = $this->I([
            [
                'url',
                null,
                's',
                'require|url',
            ],
        ]);

        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $options = (new WxLoginLogic())->JsSDKSign(self::$_input['url']);

        //**数据返回**
        if ($options)
            return $this->R(null, null, $options);

        return $this->R();
    }

    //==================================================== 登录绑定 ======================================================

    /**
     * 检测二维码
     */
    public function CheckQrCode()
    {
        $qrcode = (new WxLoginLogic())->CheckQrCode();

        //**数据返回**
        if ($qrcode)
            return $this->R(null, null, $qrcode);

        return $this->R();
    }

    /**
     * 绑定登录
     */
    public function BindLogin()
    {
        //数据接收
        $vali = $this->I([
            [
                'user_name',
                null,
                's',
                'require|length:4,20|alphaPrefix|alphaDash',
            ],
            [
                'pwd',
                null,
                's',
                'require|length:6,20|alphaDash2',
            ],
            [
                'key',
                null,
                's',
                'require|min:1',
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        (new WxLoginLogic())->BindLogin(strtolower(self::$_input['user_name']), self::$_input['pwd'], self::$_input['key']);

        return $this->R();
    }

    /**
     *
     * 手机号注册绑定
     */
    public function Register_Bind()
    {
        //数据接收
        $vali = $this->I([
            [
                'user_name',
                null,
                's',
                'require|length:4,20|alphaPrefix|alphaDash',
            ],
            [
                'pwd',
                null,
                's',
                'require|length:6,20|alphaDash2',
            ],
            [
                'phone',
                null,
                's',
                'require|mobile',
            ],
            [
                'verify_code',
                null,
                's',
                'length:4',
            ],
            [
                'key',
                null,
                's',
                'require|min:1',
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $userName    = strtolower(self::$_input['user_name']);
        $pwd         = self::$_input['pwd'];
        $mobile      = self::$_input['phone'];
        $verify_code = self::$_input['verify_code'];
        $key         = self::$_input['key'];

        //1.0 检测校验码
        $userLogic = new UserLoginLogic();
        $checked   = $userLogic->CheckVerify($mobile, SESSIONID_VERIFY_REGISTER, $verify_code, false);
        if (!$checked)
        {
            return $this->R(\EC::VERIFYCODE_ERROR);
        }

        //2.0 注册
        $rtn = $userLogic->Register($userName, $pwd, null, $mobile);
        if (!$rtn)
        {
            return $this->R();
        }

        //3.0 微信绑定
        $rtn = (new WxLoginLogic())->BindLogin($userName, $pwd, $key);
        if (!$rtn)
        {
            return $this->R();
        }

        //4.0 删除校验码
        if ($userLogic::$_error_code == \EC::SUCCESS)
            $userLogic->DelVerify($mobile, SESSIONID_VERIFY_REGISTER);

        return $this->R();
    }

    /**
     * 移除绑定登录
     */
    public function DelBind()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        (new WxLoginLogic())->DelLogin(self::$_uname);

        return $this->R();
    }

    /**
     * 是否绑定
     */
    public function IsBind()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        $rtn = (new WxLoginLogic())->IsBind(self::$_uname);

        return $this->R(null, null, ['exist' => $rtn ? YES : NO]);
    }

    //==================================================== 微信跳转 ======================================================
    /**
     * 1.0 网页授权URL - snsapi_base (仅可以获取到粉丝的openid)
     */
    public function BaseRedirect()
    {
        //数据接收
        $vali = $this->I([
            [
                'auth_url',
                null,
                's',
                'require|min:1',
            ],
            [
                'is_login',
                YES,
                'd',
                'number|in:' . YES . ',' . NO,
            ],
            [
                'is_pic',
                YES,
                'd',
                'number|in:' . YES . ',' . NO,
            ],
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $isPic = self::$_input['is_pic'];

        $redirectURL = (new WxLoginLogic())->BaseRedirect(self::$_input['auth_url'], self::$_input['is_login'],
            $isPic);
        if(YES != $isPic)
        {
            return $this->R(null, null, ['url' => $redirectURL]);
        }

        return $redirectURL;
    }

    /**
     * 2.0 通过code换取网页授权access_token
     * 3.0 获取用户的openid
     */
    public function BaseInfo($key = '', $isLogin=YES)
    {
        $redirctURL = (new WxLoginLogic())->BaseInfo($key, $isLogin);
        if(false !== $redirctURL)
        {
            return redirect($redirctURL);
        }
    }

    /**
     * 2.0 通过code换取网页授权access_token
     * 3.0 获取授权后的用户资料
     */
    public function UserInfo($key = '')
    {
        $redirctURL = (new WxLoginLogic())->UserInfo($key);
        if(false !== $redirctURL)
        {
            return redirect($redirctURL);
        }
    }
}