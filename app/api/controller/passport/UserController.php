<?php
namespace app\api\controller\passport;

use app\api\controller\BaseController;
use app\api\logic\{
    UserLoginLogic
};

/**
 * 本地登录-注册-找回密码
 */
class UserController extends BaseController
{
    public function Index()
    {
        return $this->R();
    }

    //==================================================== 登录 =========================================================
    /**
     * 登录
     */
    public function Login()
    {
        //数据接收
        $vali = $this->I([
            [
                'name', //用户名、手机号、邮箱
                null,
                's',
                'require|min:1',
            ],
            [
                'pwd',
                null,
                's',
                'require|length:1,255',
            ],
            [
                'freelogin', //30天免登录
                NO,
                'd',
                'in:' . YES . ',' . NO,
            ],
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $data = (new UserLoginLogic())->Login(self::$_input['name'], self::$_input['pwd'], self::$_input['freelogin']);

        //单设备登录
        if($data)
        {
            $this->Single_Login($data['name'], $data['sg']);
        }

        return $this->R(null, null, $data);
    }

    /**
     * 退出
     */
    public function LogOut()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        $data = (new UserLoginLogic())->Logout(self::$_uname);

        //**数据返回**
        if ($data)
            return $this->R(null, null, $data);

        return $this->R();
    }

    /**
     * 通过客户端打开 URL
     */
    public function Open_Url()
    {
        //数据接收
        $vali = $this->I([
            [
                'name', //用户名、手机号、邮箱
                null,
                's',
                'require|length:1,32',
            ],
            [
                'sg',
                null,
                's',
                'require|length:32'
            ],
            [
                'redirect_uri',
                null,
                's',
                'require|url'
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $data = (new UserLoginLogic())->OpenUrl(self::$_input['name'], self::$_input['sg']);

        //单设备登录
        if($data)
        {
            $this->Single_Login($data['name'], $data['sg']);
        }

        //重定向浏览器
        $redirctURL = self::$_input['redirect_uri'];
        if(false !== $redirctURL)
        {
            return redirect($redirctURL);
        }
    }

    //==================================================== 注册 =========================================================
    /**
     * 注册 -- 手机号
     */
    public function Register()
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

        $userLogic = new UserLoginLogic();

        // 检测校验码
        $checked = $userLogic->CheckVerify($mobile, SESSIONID_VERIFY_REGISTER, $verify_code, false);
        if (!$checked)
        {
            return $this->R(\EC::VERIFYCODE_ERROR);
        }

        // 注册
        $rtn = $userLogic->Register($userName, $pwd, null, $mobile);
        if(!$rtn)
        {
            return $this->R();
        }

        // 删除校验码
        if($userLogic::$_error_code == \EC::SUCCESS)
            $userLogic->DelVerify($mobile, SESSIONID_VERIFY_REGISTER);

        return $this->R();
    }

    /**
     * 注册 -- 邮箱
     */
    public function Register_Email()
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
                'email',
                null,
                's',
                'require|email|max:32',
            ],
            [
                'verify_code',
                null,
                's',
                'length:4',
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $userName    = strtolower(self::$_input['user_name']);
        $pwd         = self::$_input['pwd'];
        $email       = strtolower(self::$_input['email']);
        $verify_code = self::$_input['verify_code'];

        // 检测校验码
        $userLogic = new UserLoginLogic();
        $checked = $userLogic->CheckVerify($email, SESSIONID_VERIFY_REGISTER, $verify_code, false);
        if (!$checked)
        {
            return $this->R(\EC::VERIFYCODE_ERROR);
        }

        // 注册
        $rtn = $userLogic->Register($userName, $pwd, $email);
        if(!$rtn)
        {
            return $this->R();
        }

        // 删除校验码
        if($userLogic::$_error_code == \EC::SUCCESS)
            $userLogic->DelVerify($email, SESSIONID_VERIFY_REGISTER);

        return $this->R();
    }

    //==================================================== 找回密码 =====================================================
    /**
     * 找回密码 -- 邮箱
     */
    public function FindPwd_Email()
    {
        $vali = $this->I([
            [
                'email',
                null,
                's',
                'require|email|max:32',
            ],
            [
                'verify_code',
                null,
                's',
                'length:4',
            ],
            [
                'new_pwd',
                null,
                's',
                'require|length:6,20|alphaDash2',
            ],
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $new_pwd     = self::$_input['new_pwd'];
        $email       = strtolower(self::$_input['email']);
        $verify_code = self::$_input['verify_code'];

        $userLogic = new UserLoginLogic();

        //检测校验码
        $checked = $userLogic->CheckVerify($email, SESSIONID_VERIFY_FINDPWD, $verify_code, false, true);
        if (!$checked)
        {
            return $this->R(\EC::VERIFYCODE_ERROR);
        }

        $userLogic->FindPwd_Reset($email, $new_pwd);

        //删除校验码
        if($userLogic::$_error_code == \EC::SUCCESS)
            $userLogic->DelVerify($email, SESSIONID_VERIFY_FINDPWD);

        return $this->R();
    }

    /**
     * 找回密码 -- 手机号
     */
    public function FindPwd_Phone()
    {
        $vali = $this->I([
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
                'new_pwd',
                null,
                's',
                'require|length:6,20|alphaDash2',
            ],
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $new_pwd     = self::$_input['new_pwd'];
        $phone       = self::$_input['phone'];
        $verify_code = self::$_input['verify_code'];

        $userLogic = new UserLoginLogic();

        //检测校验码
        $checked = $userLogic->CheckVerify($phone, SESSIONID_VERIFY_FINDPWD, $verify_code, false, true);
        if (!$checked)
        {
            return $this->R(\EC::VERIFYCODE_ERROR);
        }

        $userLogic->FindPwd_Reset($phone, $new_pwd);

        //删除校验码
        if($userLogic::$_error_code == \EC::SUCCESS)
            $userLogic->DelVerify($phone, SESSIONID_VERIFY_FINDPWD);

        return $this->R();
    }
}