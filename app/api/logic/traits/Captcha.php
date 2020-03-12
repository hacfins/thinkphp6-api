<?php
namespace app\api\logic\traits;

use app\api\model\
{
    common\Verify
};

/*
 * 验证码 - 校验码
 */
trait Captcha
{
    //==================================================== 验证码 =======================================================
    /**
     * 生成验证码
     *
     * @param int    $len  长度
     * @param string $type 类型 （登录、注册、找回密码等）
     *
     * @return \think\Response
     */
    public function Captcha($len=4)
    {
        return  \think\captcha\facade\Captcha::create();
    }

    /**
     * 检测验证码是否正确
     *
     * @param string $verify_code 校验码
     * @param bool   $bReset      验证成功后是否重置
     * @param string $type        类型
     *
     * @return bool
     */
    public function CheckCaptcha(string $verify_code, bool $bReset=false)
    {
        // 判断是否一致
        return captcha_check($verify_code);
    }

    //==================================================== 校验码 =======================================================
    /**
     * 生成校验码
     *
     * 校验码的值保存的session中验证码保存到session的格式为：
     *  array('verify_code' => '校验码值', 'verify_time' => '校验码创建时间');
     *
     * @param string $key  手机号/邮箱
     * @param string $type 类型
     *
     * @return string
     */
    public function Verify(string $key, $type=SESSIONID_VERIFY_REGISTER)
    {
        try
        {
            $code = Verify::instance()->Verify_Generate($key, $type);

            //发送校验码
            if(validate_telphone($key))
            {
                //找回密码，需要判断账户相关信息
                if($type == SESSIONID_VERIFY_FINDPWD)
                {
                    $rtn = $this->CheckUserExist($key, true);
                    if(!$rtn)
                        return false;
                }

                if ($type == SESSIONID_VERIFY_REGISTER)
                    $type = SMS_USER_REGIETER;
                else if ($type == SESSIONID_VERIFY_MODIFY)
                    $type = SMS_MODIFY_PHONE;
                else if ($type == SESSIONID_VERIFY_FINDPWD)
                    $type = SMS_FINDPWD_PHONE;

                $status = send_sms($type, $key, [$code]);
                if($status->Code != 'OK')
                {
                    static::$_error_code = \EC::PHONE_SEND_ERROR;
                    static::$_error_msg = $status->Message;
                    return false;
                }
            }
            else if(validate_email($key))
            {
                //找回密码，需要判断账户相关信息
                if($type == SESSIONID_VERIFY_FINDPWD)
                {
                    $rtn = $this->CheckUserExist($key, false);
                    if(!$rtn)
                        return false;
                }

                $smtp       = yaconf('smtp');
                $emailTitle = $smtp['from_user'] . '账号';
                $copyRight  = $smtp['copy_right'];

                //2.0 发送p
                $nickname = $smtp['from_user'] . '用户';
                $body     = <<<EOF
        <table style='width:100%;max-width:960px;position: relative;left:0;right:0;margin: 0 auto;border-collapse: collapse;border-spacing: 0;font-size: 14px;line-height: 24px;color: #333;font-family: Microsoft YaHei;'>
      <tbody>
      <tr>
          <td style='padding: 20px 7.5% 0;'> <span style='border-bottom:1px dashed #ccc;z-index:1;' t='7' onclick='return false;' data='{$nickname}'>{$nickname}</span> ，您好！</td>
     </tr>
     
      <tr>
          <td style='padding: 20px 7.5% 0;'>为确保是您本人操作，您已选择通过该邮件地址获取验证码验证身份。请在邮件验证码输入框输入下方验证码：</td>
     </tr>
     
      <tr>
          <td style='padding: 20px 7.5% 0;'><span style='border-bottom: 1px dashed rgb(204, 204, 204); z-index: 1; position: static;font-size: 18px;color: #FF0000;' t='7' onclick='return false;' data='{$code}'>{$code}</span></td>
     </tr>

      <tr>
          <td style='padding: 20px 7.5% 0;'>勿向任何人泄露您收到的验证码。验证码会在邮件发送30分钟后失效。</td>
     </tr>
      <tr>
          <td style='padding: 20px 7.5% 117px;'>此致<br>{$emailTitle}</td>
     </tr>
  </tbody></table>
  
  <table style='width:100%;max-width:960px;position: relative;left:0;right:0;margin: 0 auto;text-align: center;border-collapse: collapse;border-spacing: 0;font-size: 12px;line-height: 24px;font-family: Microsoft YaHei;'>
     <tbody>     
  <tr>
  <td style='display: block;height: 16px;border-top:#efefef solid 1px;background: -webkit-radial-gradient(top, ellipse farthest-side, rgba(251,251,251,1), rgba(255,255,255,0));background: -o-radial-gradient(top, ellipse farthest-side, rgba(251,251,251,1), rgba(255,255,255,0));background: -moz-radial-gradient(top, ellipse farthest-side, rgba(251,251,251,1), rgba(255,255,255,0));background: radial-gradient(top, ellipse farthest-side, rgba(251,251,251,1), rgba(255,255,255,0));'></td>
  </tr>
  
 <tr>
	  <td style='color: #999;padding:0 16px 28px;'>{$copyRight}</td>
 </tr>
   </tbody>
   </tbody></table>
EOF;
                if($type == SESSIONID_VERIFY_REGISTER)
                    $emailTitle .= '用户注册';
                else if($type == SESSIONID_VERIFY_MODIFY)
                    $emailTitle .= '修改邮箱';
                else if($type == SESSIONID_VERIFY_FINDPWD)
                    $emailTitle .= '找回密码';

                return send_email($smtp, $key, $emailTitle, $body);
            }
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();
            return false;
        }
    }

    /**
     * 验证校验码是否正确
     *
     * @param string $code 用户校验码
     * @param string $type 校验码类型
     *
     * @return bool 用户校验码是否正确
     */
    public function CheckVerify(string $key, $type=SESSIONID_VERIFY_REGISTER,  string $code='', bool $delSession = false,
        bool $bCheck=false)
    {
        $SwitchInfo = $this->GetSwitch();
        $check = $SwitchInfo['check'] ?? YES;
        if ($check == NO && $bCheck === false)
        {
            return true;
        }

        return Verify::instance()->Verify_Check($key, $type, $code, $delSession);
    }

    /**
     * 删除校验码
     *
     * @param string $key key
     * @param string $type 校验码类型
     *
     * @return bool 用户校验码是否正确
     */
    public function DelVerify(string $key, $type=SESSIONID_VERIFY_REGISTER)
    {
        Verify::instance()->Verify_Del($key, $type);
    }
}