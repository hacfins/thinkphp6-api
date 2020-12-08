<?php

namespace app\api\controller\passport;

use app\api\controller\BaseController;
use app\api\logic\
{UserLoginLogic, SnsLoginLogic};

/**
 * 第三方登录、绑定
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
        $param = $this->I([
            [
                'url',
                null,
                's',
                'require|url',
            ],
        ]);

        $options = (new SnsLoginLogic(USEROAUTHS_TYPE_WEIXIN))
            ->JsSDKSign($param['url']);

        //**数据返回**
        if ($options)
        {
            return $this->R(null, null, $options);
        }

        return $this->R();
    }

    //==================================================== 登录绑定 ======================================================
    /**
     * 检测二维码
     */
    public function CheckQrCode()
    {
        //数据接收
        $param = $this->I([
            [
                'type',
                USEROAUTHS_TYPE_WEIXIN,
                's',
            ],
        ]);

        $qrcode = (new SnsLoginLogic($param['type']))->CheckQrCode();

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
        $param = $this->I([
            [
                'type',
                USEROAUTHS_TYPE_WEIXIN,
                's',
            ],
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

        (new SnsLoginLogic($param['type']))->BindLogin(strtolower($param['user_name']),
            $param['pwd'], $param['key']);

        return $this->R();
    }

    /**
     *
     * 手机号注册绑定
     */
    public function Register_Bind()
    {
        //数据接收
        $param = $this->I([
            [
                'type',
                USEROAUTHS_TYPE_WEIXIN,
                's',
            ],
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

        $userName    = strtolower($param['user_name']);
        $pwd         = $param['pwd'];
        $mobile      = $param['phone'];
        $verify_code = $param['verify_code'];
        $key         = $param['key'];

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

        //3.0 绑定
        $rtn = (new SnsLoginLogic($param['type']))->BindLogin($userName, $pwd, $key);
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

        //数据接收
        $param = $this->I([
            [
                'type',
                USEROAUTHS_TYPE_WEIXIN,
                's',
            ],
        ]);

        (new SnsLoginLogic($param['type']))->DelLogin(self::$_uname);

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

        //数据接收
        $param = $this->I([
            [
                'type',
                USEROAUTHS_TYPE_WEIXIN,
                's',
            ],
        ]);

        $rtn = (new SnsLoginLogic($param['type']))->IsBind(self::$_uname);

        return $this->R(null, null, ['exist' => $rtn ? YES : NO]);
    }

    //==================================================== 跳转 ======================================================
    /**
     * 1.0 网页授权URL - snsapi_base (仅可以获取到粉丝的openid)
     */
    public function BaseRedirect()
    {
        //数据接收
        $param = $this->I([
            [
                'type',
                USEROAUTHS_TYPE_WEIXIN,
                's',
            ],
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

        $isPic = $param['is_pic'];

        $redirectURL = (new SnsLoginLogic($param['type']))->BaseRedirect($param['auth_url'],
            $param['is_login'],
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
        //数据接收
        $param = $this->I([
            [
                'type',
                USEROAUTHS_TYPE_WEIXIN,
                's',
            ],
        ]);

        $redirctURL = (new SnsLoginLogic($param['type']))->BaseInfo($key, $isLogin);
        if(false !== $redirctURL)
        {
            return redirect($redirctURL);
        }

        return $this->R();
    }

    /**
     * 2.0 通过code换取网页授权access_token
     * 3.0 获取授权后的用户资料
     */
    public function UserInfo($key = '')
    {
        //数据接收
        $param = $this->I([
            [
                'type',
                USEROAUTHS_TYPE_WEIXIN,
                's',
            ],
        ]);

        $redirctURL = (new SnsLoginLogic($param['type']))->UserInfo($key);
        if(false !== $redirctURL)
        {
            return redirect($redirctURL);
        }

        return $this->R();
    }
}