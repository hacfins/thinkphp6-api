<?php

namespace app\api\controller\user;

use app\api\controller\BaseController;
use app\api\logic\
{BaseLogic, UploadLogic, UserLogic, UserLoginLogic};

/**
 * 用户信息
 */
class UserController extends BaseController
{
    public function Index()
    {
        return $this->R();
    }

    //==================================================== 修改  ========================================================

    /**
     * 修改手机号
     */
    public function ModifyPhone()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //数据接收
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
                'pwd',
                null,
                's',
                'require|length:6,20|alphaDash2',
            ],
        ]);

        $phone       = $param['phone'];
        $verify_code = $param['verify_code'];
        $pwd         = $param['pwd'];

        $userLogic = new UserLoginLogic();

        //检测校验码
        $checked = $userLogic->CheckVerify($phone, SESSIONID_VERIFY_MODIFY, $verify_code, false);
        if (!$checked)
        {
            return $this->R(\EC::VERIFYCODE_ERROR);
        }

        (new UserLogic())->ModifyPhone($phone, $pwd);

        //删除校验码
        if ($userLogic::$_error_code == \EC::SUCCESS)
            $userLogic->DelVerify($phone, SESSIONID_VERIFY_MODIFY);

        return $this->R();
    }

    /**
     * 修改邮箱
     */
    public function ModifyEmail()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //数据接收
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
                'pwd',
                null,
                's',
                'require|length:6,20|alphaDash2',
            ],
        ]);

        $email       = strtolower($param['email']);
        $verify_code = $param['verify_code'];
        $pwd         = $param['pwd'];

        $userLogic = new UserLoginLogic();

        //检测校验码
        $checked = $userLogic->CheckVerify($email, SESSIONID_VERIFY_MODIFY, $verify_code, false);
        if (!$checked)
        {
            return $this->R(\EC::VERIFYCODE_ERROR);
        }

        (new UserLogic())->ModifyEmail($email, $pwd);

        //删除校验码
        if ($userLogic::$_error_code == \EC::SUCCESS)
            $userLogic->DelVerify($email, SESSIONID_VERIFY_MODIFY);

        return $this->R();
    }

    /**
     * 修改密码
     */
    public function ModifyPwd()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //数据接收
        $param = $this->I([
            [
                'old_pwd',
                null,
                's',
                'require|length:6,20|alphaDash2',
            ],
            [
                'new_pwd',
                null,
                's',
                'require|length:6,20|alphaDash2',
            ],
        ]);

        (new UserLogic())->ModifyPwd($param['old_pwd'], $param['new_pwd']);

        return $this->R();
    }

    /**
     * 他人修改密码
     */
//    public function ModifyPwd_Admin()
//    {
//        if(!$this->NeedToken())
//        {
//            return $this->R();
//        }
//
//        //数据接收
//        $vali = $this->I([
//            [
//                'user_name',
//                null,
//                's',
//                'require|length:1,20',
//            ],
//            [
//                'new_pwd',
//                null,
//                's',
//                'require|length:6,20|alphaDash2',
//            ],
//        ]);
//        if ($vali !== true)
//        {
//            return $this->R(\EC::PARAM_ERROR, null, $vali);
//        }
//
//        (new UserLogic())->ModifyPwd_Admin(self::$_input['user_name'], self::$_input['new_pwd']);
//
//        return $this->R();
//    }

    /**
     * 修改信息
     */
    public function ModifyInfo()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //数据接收
        $param = $this->I([
            [
                'nick_name',
                null,
                's',
                'length:2,20'
            ],
            [
                'full_name',
                null,
                's',
                'length:2,20'
            ],
            [
                'sex',
                null,
                'd',
                'in:' . USER_SEX_MAN . ',' . USER_SEX_WOMEN . ',' . USER_SEX_UNKOWN,
            ],
            [
                'birthday',
                null,
                's',
                'dateFormat:Y-m-d'
            ],
            [
                'adcode', //区域代码
                null,
                'd',
                '>:0'
            ],
            [
                'company',
                null,
                's',
                'length:1,40'
            ],
            [
                'description',
                null,
                's',
                'max:128'
            ]
        ]);

        //Todo:adcode 校验
        (new UserLogic())->ModifyInfo(self::$_uname, $param['nick_name'], $param['full_name'],
            $param['sex'], $param['birthday'], $param['adcode'], $param['company'],
            $param['description']);

        return $this->R();
    }

    /**
     * 修改信息
     */
    public function ModifyInfo_Admin()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //数据接收
        $param = $this->I([
            [
                'user_name',
                null,
                's',
                'length:2,20'
            ],
            [
                'nick_name',
                null,
                's',
                'length:2,20'
            ],
            [
                'full_name',
                null,
                's',
                'length:2,20'
            ],
            [
                'sex',
                null,
                'd',
                'in:' . USER_SEX_MAN . ',' . USER_SEX_WOMEN . ',' . USER_SEX_UNKOWN,
            ],
            [
                'birthday',
                null,
                's',
                'dateFormat:Y-m-d'
            ],
            [
                'adcode', //区域代码
                null,
                'd',
                '>:0'
            ],
            [
                'company',
                null,
                's',
                'length:1,40'
            ],
            [
                'description',
                null,
                's',
                'max:128'
            ],
            [
                'new_pwd',
                null,
                's',
                'length:6,20|alphaDash2',
            ],
            [
                'email',
                null,
                's',
                'email|max:32',
            ],
            [
                'phone',
                null,
                's',
                'mobile',
            ]
        ]);

        //Todo:adcode 校验
        (new UserLogic())->ModifyInfo($param['user_name'], $param['nick_name'], $param['full_name'],
            $param['sex'], $param['birthday'], $param['adcode'], $param['company'],
            $param['description'], $param['new_pwd'], $param['email'], $param['phone'],YES);

        return $this->R();
    }

    //==================================================== 头像  ========================================================

    /**
     * 上传
     */
    public function UploadAvator()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        $imgPath = UploadLogic::UploadImg(DIR_IMGS_USERS);

        if ($imgPath)
        {
            $imgPath = UploadLogic::GetImgLocalUrl($imgPath);
        }

        //**数据返回**
        return $this->R(null, null, ['img' => $imgPath ? $imgPath : '']);
    }

    /**
     * 头像保存
     */
    public function SaveAvator()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //数据接收
        $param = $this->I([
            [
                'img',
                null,
                's',
                'require|url',
            ],
            [
                'width',
                null,
                'd',
                'number|>:0',
            ],
            [
                'height',
                null,
                'd',
                'number|>:0',
            ],
            [
                'sx',
                0,
                'd',
                'number|>=:0',
            ],
            [
                'sy',
                0,
                'd',
                'number|>=:0',
            ],
        ]);

        $imgPath = (new UserLogic())->ModifyAvator($param['img'], $param['width'], $param['height'],
            $param['sx'], $param['sy']);

        return $this->R(null, null, ['img' => $imgPath ? $imgPath : '']);
    }

    //==================================================== 信息  ========================================================

    /**
     * 获取用户信息
     */
    public function Info()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        $userLogic = (new UserLogic());
        $userInfo  = $userLogic->GetInfo(self::$_uname);

        if ($userInfo)
        {
            unset($userInfo['reg_ip']);
            unset($userInfo['status']);

            $userInfo['adcode_name'] = '';
            $adName                  = $userLogic->GetAdCodeInfo($userInfo['adcode']);

            if ($adName)
                $userInfo['adcode_name'] = $adName;

            $userInfo['user_name'] = self::$_uname;
        }

        return $this->R(null, null, $userInfo);
    }

    /**
     * 获取他人信息
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function Others_Info()
    {
        //数据接收
        $param = $this->I([
            [
                'user_name',
                null,
                's',
                'require|length:2,20'
            ]
        ]);

        $info = (new UserLogic())->GetOthersInfo($param['user_name']);

        //**数据返回**
        if ($info)
            return $this->R(null, null, $info);

        return $this->R();
    }

    /**
     * 获取用户信息(扩展)
     */
    public function Info_Ex()
    {
        //数据接收
        $param = $this->I([
            [
                'user_name',
                null,
                's',
                'require|length:4,20000|alphaPrefix',
            ],
        ]);

        $userNames = ids2array($param['user_name']);
        $userLogic = (new UserLogic());

        $rtn = [];
        foreach ($userNames as $userName)
        {
            $userInfo  = $userLogic->GetInfo($userName);
            if ($userInfo)
            {
                $arr['user_name']   = $userName;
                $arr['nick_name']   = $userInfo['nick_name'];
                $arr['full_name']   = $userInfo['full_name'];
                $arr['sex']         = $userInfo['sex'];
                $arr['avator']      = $userInfo['avator'];
                $arr['description'] = $userInfo['description'];
                $arr['adcode']      = $userInfo['adcode'];
                $arr['company']     = $userInfo['company'];
                $arr['birthday']    = $userInfo['birthday'];

                $rtn[] = $arr;
            }
        }

        //单个用户信息，直接返回非数组的信息
        if(1 == count($userNames))
        {
            $rtn = $rtn[0] ?? [];
        }
        else
        {
            //多个用户时，不报告用户不存在的消息
            BaseLogic::$_error_code = \EC::SUCCESS;
        }

        return $this->R(null, null, $rtn);
    }
}