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
        $param = $this->I([
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

        $data = (new UserLoginLogic())->Login($param['name'], $param['pwd'], $param['freelogin']);

        //单设备登录
        if($data)
        {
            $this->Single_Login($data['name'], $data['sg']);
        }

        return $this->R(null, null, $data);
    }

    /**
     * 登录 -- 手机号校验码
     */
    public function Login_Verify()
    {
        $param = $this->I([
            [
                'name', //手机号、邮箱
                null,
                's',
                'require|min:1',
            ],
            [
                'verify_code',
                null,
                's',
                'length:4',
            ],
            [
                'freelogin', //30天免登录
                NO,
                'd',
                'in:' . YES . ',' . NO,
            ],
        ]);

        $userLogic = new UserLoginLogic();

        //检测校验码
        $checked = $userLogic->CheckVerify($param['name'], SESSIONID_VERIFY_LOGIN, $param['verify_code'],
            false, true);
        if (!$checked)
        {
            return $this->R(\EC::VERIFYCODE_ERROR);
        }

        $data = (new UserLoginLogic())->Login_Verify($param['name'], $param['freelogin']);

        //单设备登录
        if($data)
        {
            $this->Single_Login($data['name'], $data['sg']);
        }

        //删除校验码
        if($userLogic::$_error_code == \EC::SUCCESS)
            $userLogic->DelVerify($param['name'], SESSIONID_VERIFY_LOGIN);

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
        $param = $this->I([
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

        $data = (new UserLoginLogic())->OpenUrl($param['name'], $param['sg']);

        //单设备登录
        if($data)
        {
            $this->Single_Login($data['name'], $data['sg']);
        }

        //重定向浏览器
        $redirctURL = $param['redirect_uri'];
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
        $param = $this->I([
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

        $userName    = strtolower($param['user_name']);
        $pwd         = $param['pwd'];
        $mobile      = $param['phone'];
        $verify_code = $param['verify_code'];

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
        $param = $this->I([
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

        $userName    = strtolower($param['user_name']);
        $pwd         = $param['pwd'];
        $email       = strtolower($param['email']);
        $verify_code = $param['verify_code'];

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
        $param = $this->I([
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

        $new_pwd     = $param['new_pwd'];
        $email       = strtolower($param['email']);
        $verify_code = $param['verify_code'];

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
        $param = $this->I([
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

        $new_pwd     = $param['new_pwd'];
        $phone       = $param['phone'];
        $verify_code = $param['verify_code'];

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