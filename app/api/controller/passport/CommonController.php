<?php
namespace app\api\controller\passport;

use app\api\controller\BaseController;
use app\api\logic\
{
    UserLoginLogic
};

/**
 * 验证码、校验码、用户名|邮箱|手机号是否存在
 */
class CommonController extends BaseController
{
    public function Index()
    {
        return $this->R();
    }

    //==================================================== 是否存在 =====================================================
    /**
     * 检查用户名是否存在
     */
    public function ExistName()
    {
        //数据接收
        $param = $this->I([
            [
                'user_name',
                null,
                's',
                'require|alphaPrefix|alphaDash|length:4,20',
            ],
        ]);

        $rst = (new UserLoginLogic())->ExistName(strtolower($param['user_name']));

        return $this->R(null, null, ['exist' => $rst]);
    }

    /**
     * 检查邮箱是否存在
     */
    public function ExistEmail()
    {
        //数据接收
        $param = $this->I([
            [
                'email',
                null,
                's',
                'require|email|max:32',
            ],
            [
                'except', //是否排除自己
                YES,
                'd',
                'in:' . YES . ',' . NO,
            ],
            [
                'user_name',
                null,
                's',
                'alphaPrefix|alphaDash|length:4,20',
            ],
        ]);

        $rst = (new UserLoginLogic())->ExistEmail(strtolower($param['email']), $param['except'], $param['user_name']);

        return $this->R(null, null, ['exist' => $rst]);
    }

    /**
     * 检查手机号是否存在
     */
    public function ExistPhone()
    {
        //数据接收
        $param = $this->I([
            [
                'phone',
                null,
                's',
                'require|mobile',
            ],
            [
                'except', //是否排除自己
                YES,
                'd',
                'in:' . YES . ',' . NO,
            ],
            [
                'user_name',
                null,
                's',
                'alphaPrefix|alphaDash|length:4,20',
            ],
        ]);

        $rst = (new UserLoginLogic())->ExistPhone($param['phone'], $param['except'], $param['user_name']);

        return $this->R(null, null, ['exist' => $rst]);
    }

    //==================================================== 验证码|校验码 =================================================
    /**
     * 获取验证码
     */
    public function Captcha()
    {
        //数据接收
        $param = $this->I([
            [
                'len',
                4,
                'd',
                'between:4,6',
            ]
        ]);

        return (new UserLoginLogic())->Captcha($param['len']);
    }

    /**
     * 检测验证码是否正确
     */
    public function CheckCaptcha()
    {
        //数据接收
        $param = $this->I([
            [
                'captcha_code',
                null,
                's',
                'require|length:4,6',
            ]
        ]);

        $checked = (new UserLoginLogic())->CheckCaptcha($param['captcha_code']);

        return $this->R($checked ? \EC::SUCCESS : \EC::CAPTCHA_ERROR);
    }

    /**
     * 获取校验码
     */
    public function Verify()
    {
        //数据接收
        $param = $this->I([
            [
                'name', //手机号 | 邮箱
                null,
                's',
                'require|min:1'
            ],
            [
                'type',
                SESSIONID_VERIFY_REGISTER,
                'd',
                'in:' . SESSIONID_VERIFY_SET,
            ]
        ]);

        //Todo: 防止攻击
        (new UserLoginLogic())->Verify(strtolower($param['name']), $param['type']);

        return $this->R();
    }

    /**
     * 检测校验码是否正确
     */
    public function CheckVerify()
    {
        //数据接收
        $param = $this->I([
            [
                'name', //手机号 | 邮箱
                null,
                's',
                'require|min:1'
            ],
            [
                'type',
                SESSIONID_VERIFY_REGISTER,
                'd',
                'in:' . SESSIONID_VERIFY_SET,
            ],
            [
                'verify_code',
                null,
                's',
                'require|length:4',
            ]
        ]);

        //检测校验码
        $checked = (new UserLoginLogic())->CheckVerify(strtolower($param['name']), $param['type'],
            $param['verify_code'], false, true);

        return $this->R($checked ? \EC::SUCCESS : \EC::VERIFYCODE_ERROR);
    }
}