<?php

namespace app\api\logic;

use app\api\logic\traits\UserOp;
use app\api\model\
{
    location\AreaArea,
    location\AreaCity,
    location\AreaProvince,
    log\UserLogs
};
use app\api\model\rbac\
{
    UserRoles, UserTokens, User, UserAuth
};
use app\common\third\Apache;
use think\facade\Db;
use think\facade\Cache;

/**
 * 用户信息 添加、修改、获取 等
 */
class UserLogic extends BaseLogic
{
    use UserOp;

    // +--------------------------------------------------------------------------
    // |  基本操作
    // +--------------------------------------------------------------------------
    /**
     * 添加用户
     *
     * @param string      $name
     * @param string|null $full_name
     * @param string|null $phone
     * @param int|null    $sex
     */
    public function Add(
        string $name, string $full_name = null, string $phone = null, int $sex = USER_SEX_UNKOWN,
        string $company = '')
    {
        //检测用户名是否存在
        $user     = User::instance();
        if ($user->CheckExist($name))
        {
            static::$_error_code = \EC::USER_EXIST_ERROR;

            return false;
        }
        //检测手机号是否存在
        if (isset($phone) && $phone != '')
        {
            $userAuth = UserAuth::instance();
            if ($userAuth->CheckExist_Phone($phone))
            {
                static::$_error_code = \EC::USER_PHONE_EXIST_ERROR;

                return false;
            }
        }
        else
        {
            $phone = '';
        }

        Db::startTrans();
        try
        {
            //创建用户
            $user->Add($name, null, $full_name, $sex, '', 0, $company);
            UserAuth::instance()->Add($name, $name . DEF_USER_PWD, $phone);
            if ($name != USER_NAME_ADMIN)
            {
                //2.0 设置角色为普通用户
                (UserRoles::instance())->ModifyRoleByUser($name, [ROLE_USER_ROLE]);
            }

            //添加用户统计-日志
            $this->Lg_AddUser($name);

            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        //发送通知
    }

    /**
     * 修改信息
     *
     * @param string|null $userName 不传时，修改当前登录用户的信息
     */
    public function ModifyInfo(
        string $userName = null, string $nick_name = null, string $full_name = null,
        int $sex = null, string $birthday = null, string $adcode = null, string $company = null,
        string $description = null, string $newPwd = null, string $email = null, string $phone = null, int $is_admin = NO)
    {
        if (is_null($userName))
        {
            $userName = self::$_uname;
        }

        Db::startTrans();
        try
        {
            $user = User::instance();

            //修改用户信息
            $user->Modify($userName, $nick_name, $full_name, $sex, null, $adcode, $company, $birthday, $description);

            if ($is_admin == YES)
            {
                if ($newPwd)
                {
                    $this->ModifyPwd_Admin($userName, $newPwd);
                }

                if (isset($email))
                {
                    $this->ModifyEmail($email, null, $userName);
                }

                if (isset($phone))
                {
                    $this->ModifyPhone($phone, null, $userName);
                }
            }

            //操作日志
            $uL = UserLogs::instance();
            $uL->Add($userName, USERLOGOP_OP_TYPE_MODIFY, '修改个人信息', 'user', $userName);

            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    /**
     * 修改密码
     *
     * @param string $oldPwd
     * @param string $newPwd
     */
    public function ModifyPwd(string $oldPwd, string $newPwd)
    {
        if (!self::$_uname)
        {
            static::$_error_code = \EC::USER_NOTLOGIN_ERROR;

            return false;
        }

        $userName  = self::$_uname;
        $userToken = self::$_token;

        $user = User::instance();
        if (!$user->CheckExist($userName))
        {
            static::$_error_code = \EC::USER_NOTEXIST_ERROR;

            return false;
        }

        Db::startTrans();
        try
        {
            $userAuth = UserAuth::instance();
            $userInfo = $userAuth->GetInfo($userName, false);
            $pwd      = $userInfo['pwd'];

            // 判断老密码是否正确
            if (!$this->Check_Pwd($pwd, $oldPwd))
            {
                E(\EC::USER_OLD_PWD_ERR, null, false);
            }

            // 判断新旧密码是否相同
            if ($this->Check_Pwd($pwd, $newPwd))
            {
                E(\EC::USER_OLD_PWD, null, false);
            }

            if ($userName == $newPwd)
            {
                E(\EC::USER_PASSWD_SAME_ERROR, null, false);
            }
            $userAuth->ModifyPwd($userName, $newPwd);

            //非当前客户端下线
            $aot    = UserTokens::instance();
            $tokens = $aot->GetOtherTokens($userName, null, $userToken);
            if (is_array($tokens))
            {
                $aot->DelByTokens($tokens);
            }

            //操作日志
            $uL = UserLogs::instance();
            $uL->Add($userName, USERLOGOP_OP_TYPE_MODIFY, '修改密码', 'user', $userName);

            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        //发送通知
    }

    /**
     * 修改密码
     *
     * @param string $oldPwd
     * @param string $newPwd
     */
    public function ModifyPwd_Admin(string $userName, string $newPwd)
    {
        if (!self::$_uname)
        {
            static::$_error_code = \EC::USER_NOTLOGIN_ERROR;

            return false;
        }

        $user = User::instance();
        if (!$user->CheckExist($userName))
        {
            static::$_error_code = \EC::USER_NOTEXIST_ERROR;

            return false;
        }

        Db::startTrans();
        try
        {
            $userAuth = UserAuth::instance();
            $userInfo = $userAuth->GetInfo($userName, false);

            if ($userName == $newPwd)
            {
                E(\EC::USER_PASSWD_SAME_ERROR, null, false);
            }
            $userAuth->ModifyPwd($userName, $newPwd);

            //非当前客户端下线
            $aot    = UserTokens::instance();
            $tokens = $aot->GetOtherTokens($userName);
            if (is_array($tokens))
            {
                $aot->DelByTokens($tokens);
            }

            //操作日志
            $uL = UserLogs::instance();
            $uL->Add($userName, USERLOGOP_OP_TYPE_MODIFY, '修改密码', 'user', $userName);

            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        //发送通知
    }

    /**
     * 修改手机号
     *
     * @param string $newPhone
     * @param string $pwd
     */
    public function ModifyPhone(string $newPhone, string $pwd = null, string $name = null)
    {
        if ($name)
        {
            $userName = $name;
        }
        else
        {
            if (!self::$_uname)
            {
                static::$_error_code = \EC::USER_NOTLOGIN_ERROR;

                return false;
            }

            $userName = self::$_uname;
        }

        $user = User::instance();
        if (!$user->CheckExist($userName))
        {
            static::$_error_code = \EC::USER_NOTEXIST_ERROR;

            return false;
        }

        Db::startTrans();
        try
        {
            $userAuth = UserAuth::instance();

            //3.0 验证密码
            $loginInfo = $userAuth->GetInfo($userName);

            //验证密码
            if ($pwd)
            {
                if (!$this->Check_Pwd($loginInfo['pwd'], $pwd))
                {
                    E(\EC::USER_PASSWD_ERROR, null, false);
                }
            }

            $userAuth->ModifyPhone($userName, $newPhone);

            //操作日志
            if (!$name)
            {
                $uL = UserLogs::instance();
                $uL->Add($userName, USERLOGOP_OP_TYPE_MODIFY, '修改手机号', 'user', $userName);
            }

            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        //发送通知
    }

    /**
     * 修改邮箱
     *
     * @param string $newEmail
     * @param string $pwd
     */
    public function ModifyEmail(string $newEmail, string $pwd = null, string $name = null)
    {
        if ($name)
        {
            $userName = $name;
        }
        else
        {
            if (!self::$_uname)
            {
                static::$_error_code = \EC::USER_NOTLOGIN_ERROR;

                return false;
            }

            $userName = self::$_uname;
        }

        $user = User::instance();
        if (!$user->CheckExist($userName))
        {
            static::$_error_code = \EC::USER_NOTEXIST_ERROR;

            return false;
        }

        Db::startTrans();
        try
        {
            $userAuth = UserAuth::instance();

            //3.0 验证密码
            $loginInfo = $userAuth->GetInfo($userName);

            //验证密码
            if ($pwd)
            {
                if (!$this->Check_Pwd($loginInfo['pwd'], $pwd))
                {
                    E(\EC::USER_PASSWD_ERROR, null, false);
                }
            }

            $userAuth->ModifyEmail($userName, $newEmail);

            //操作日志
            if (!$name)
            {
                $uL = UserLogs::instance();
                $uL->Add($userName, USERLOGOP_OP_TYPE_MODIFY, '修改邮箱', 'user', $userName);
            }

            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        //发送通知
    }

    /**
     * 禁用或启用用户，用 | 分割
     */
    public function Disable_Or_Enables(string $user_names, $bDisabled = true)
    {
        //授权检测
        $user_names = ids2array($user_names);
        if (in_array_case(USER_NAME_ADMIN, $user_names))
        {
            static::$_error_code = \EC::USER_ADMIN_DELETE_ERROR;

            return false;
        }

        Db::startTrans();
        $rows = 0;
        try
        {
            foreach ($user_names as $name)
            {
                $user      = User::instance();
                $loginInfo = $user->GetInfo($name);
                if (!$loginInfo)
                {
                    E(\EC::USER_NOTEXIST_ERROR, null, false);
                }

                //验证账户是否可用
                if ($bDisabled)
                {
                    if ($loginInfo['status'] !== USER_STATUS_ENABLED)
                    {
                        E(\EC::USER_DISABLE_ERROR, null, false);
                    }
                }
                else
                {
                    if ($loginInfo['status'] == USER_STATUS_ENABLED)
                    {
                        E(\EC::USER_ACTIVE_ERROR, null, false);
                    }
                }
            }

            if (!$bDisabled)
            {
                //启用用户
                $rows = $user->Enabled($user_names);
            }
            else
            {
                //禁用用户
                $rows = $user->Disabled($user_names);
            }

            //删除相关令牌
            $aot = UserTokens::instance();
            $aot->DelByUsers($user_names);

            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }

        return $rows;
    }

    /**
     * 头像保存
     * 最终头像文件的格式: mz.user.imgpath , mz.user.imgpath.'_ps_'.Img::(DEFN_L_HP/DEFN_M_HP/DEFN_H_HP)
     */
    public function ModifyAvator(string $url, $width = null, $height = null, int $sx = 0, int $sy = 0)
    {
        //1.0 如果是本地文件，需要上传到远程图片服务器
        $rUrl = $this->CheckImgPath($url);
        if (!$rUrl)
        {
            return false;
        }

        //2.0 执行高级处理操作
        $rPath = $rUrl;
        if (!empty($width) && !empty($height))
        {
            $rtn = img_mogr($rUrl, $width . 'x' . $height . 'a' . $sx . 'a' . $sy);
            if ($rtn['code'] != \EC::SUCCESS)
            {
                static::$_error_code = $rtn['code'];

                return false;
            }

            $rPath = $rtn['result']['path'];
        }

        //3.0 保存用户头像
        $user = User::instance();
        $user->ModifyAvator(self::$_uname, $rPath);

        //操作日志
        $uL = UserLogs::instance();
        $uL->Add(self::$_uname, USERLOGOP_OP_TYPE_MODIFY, '修改头像', 'user', self::$_uname);

        return $rPath;
    }

    /**
     * 获取用户信息
     *
     * @param string $user_name
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetInfo(string $user_name)
    {
        //用户信息
        $user     = User::instance();
        $userInfo = $user->GetInfo($user_name);
        if (!$userInfo)
        {
            static::$_error_code = \EC::USER_NOTEXIST_ERROR;

            return [];
        }

        $userAuthInfo = UserAuth::instance()->GetInfo($user_name);
        unset($userAuthInfo['pwd']);

        return array_merge($userInfo, $userAuthInfo);
    }

    /**
     * 获取其他用户信息
     *
     * @param string $user_name
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetOthersInfo(string $user_name)
    {
        //用户信息
        $user = User::instance();

        $userInfo = $user->GetInfo($user_name);

        //1.0 判断信息是否获取成功
        if (!$userInfo)
        {
            static::$_error_code = \EC::USER_NOTEXIST_ERROR;

            return [];
        }

        return $userInfo;
    }

    /**
     * 检测用户是否可用
     *
     * @param string $user_name
     *
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function CheckEnabled(string $user_name)
    {
        //用户信息
        $user     = User::instance();
        $userInfo = $user->GetInfo($user_name);
        if (!$userInfo)
        {
            static::$_error_code = \EC::USER_NOTEXIST_ERROR;

            return false;
        }

        //验证账户是否可用
        if ($userInfo['status'] !== USER_STATUS_ENABLED)
        {
            static::$_error_code = \EC::USER_DISABLE_ERROR;

            return false;
        }

        return true;
    }

    /**
     * 根据 area ，获取 省-市-区
     */
    public function GetAdCodeInfo(int $area_code)
    {
        do
        {
            $areaInfo = AreaArea::instance()->GetInfo($area_code);
            if (!$areaInfo)
                break;

            $cityInfo = AreaCity::instance()->GetInfo($areaInfo['city_code']);
            if (!$cityInfo)
                break;

            $provinceInfo = AreaProvince::instance()->GetInfo($areaInfo['province_code']);
            if (!$provinceInfo)
                break;

            return $provinceInfo['name'] . '|' . $cityInfo['name'] . '|' . $areaInfo['name'];
        } while (0);

        return false;
    }

    /**
     * 用户列表
     *
     * @param string|null $user_name_key
     * @param string|null $full_name_key
     * @param string|null $phoneKey
     * @param string|null $keyWord
     * @param int|null    $sex
     * @param null        $create_st
     * @param null        $create_et
     * @param bool        $all
     * @param int         $page
     * @param int         $pageSize
     *
     * @return array
     */
    public function GetList(
        string $user_name_key = null, string $full_name_key = null, string $phoneKey = null, int $sex = null,
        $create_st = null, $create_et = null, bool $all = false, string $order_field = null, int $is_asc = YES, $page = DEF_PAGE, $pageSize = DEF_PAGE_SIZE)
    {
        try
        {
            $user  = User::instance();
            $users = $user->GetList($user_name_key, $full_name_key, $phoneKey, $sex, $create_st, $create_et, $all, $order_field, $is_asc, $page, $pageSize);

            if ($users[1] > 0)
            {
                foreach ($users[0] as $k => &$user)
                {
                    unset($user['description']);

                    //手机号-邮箱
                    $userAuthInfo = UserAuth::instance()->GetInfo($user['user_name']);
                    unset($userAuthInfo['pwd']);

                    $user = array_merge($user, $userAuthInfo);
                }
            }

            return $users;
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    // +--------------------------------------------------------------------------
    // |  用户导入
    // +--------------------------------------------------------------------------
    //==================================================== 批量导入 ======================================================
    /**
     * 批量导入-下载批量导入模板
     */
    public function BatchModel($bCheck = YES)
    {
        if (!is_file(IMPORTUSERS_FILE))
        {
            self::$_error_msg = \EC::FILE_NOTEXIST_ERROR;

            return false;
        }

        if ($bCheck == YES)
        {
            return true;
        }

        return download_file(IMPORTUSERS_FILE, '导入模版.xls');
    }

    /**
     * 批量导入-下载批量导入用户的结果报表
     */
    public function BatchUpload($sourceFile)
    {
        //【1】 准备数据
        try
        {
            $user = User::instance();

            //1.0 读取数据
            $rows      = $user->ReadFile($sourceFile);
            $total     = count($rows);             //总共多少条
            $startPCol = 6 + 1;                    //日志从哪一列追加（有效字段为 6 个）

            //2.1 标记不合法的数据
            $errArray = $user->CheckValue($rows);

            //2.2 删除空行的错误标记
            $total -= count($errArray['errNone']); //空行不计入总数
            if ($total > 1000)
            {
                self::$_error_code = \EC::PARAM_ERROR;
                self::$_error_msg  = '每次不能超过 1000 条';

                return false;
            }

            //2.3 删除重复，不合法的数据
            $user->DeleteErrData($rows, $errArray);
        }
        catch (\Throwable $e)
        {
            self::$_error_code = $e->getCode();
            self::$_error_msg  = $e->getMessage();

            return false;
        }

        //【2】 入库
        //3.0 将正确的文件入库,并记录保存失败的
        $errDB = $this->ImportData($rows);
        if (false === $errDB)
        {
            return false;
        }

        $err        = 0;
        $rptFile    = '';
        $reportFile = '';
        if (isset($errDB))
        {
            //5.1 合并错误标记
            $errArray = array_merge($errArray, $errDB);

            //5.1 生成报表
            $reportFile = $user->BatchReport($sourceFile, $errArray, $startPCol);

            //6.0 返回结果
            $rptFile = guid();
            Cache::set(CACHE_UPLOAD_FILE . $rptFile, $reportFile, CACHE_TIME_SQL);

            $errAll = [];
            foreach ($errArray as $key => $v)
            {
                if ($v)
                {
                    $errAll = array_merge($errAll, $v);
                }
            }
            $errAll = array_unique($errAll);
            $err    = count($errAll) - count($errArray['errNone']); //成功多少条
        }

        $rst = [
            'total'          => $total,
            'error'          => $err,
            'report_file_id' => $rptFile,
            'err_rows'       => $errArray,
        ];

        return $rst;
    }

    /**
     * 批量导入-下载批量导入用户的结果报表
     */
    public function GetReport(string $fid, $bCheck = YES)
    {
        //解密文件路径
        $filePath = Cache::get(CACHE_UPLOAD_FILE . $fid);
        if (!is_file($filePath))
        {
            self::$_error_msg = \EC::FILE_NOTEXIST_ERROR;

            return false;
        }

        if ($bCheck == YES)
        {
            return true;
        }

        Apache::Download($filePath, '导入报表.xls');
    }

    /***
     * 入库
     *
     * @param array $rows
     *
     * @return array|bool
     */
    public function ImportData($rows)
    {
        $errDB = $errDBNameDup = $errDBPhoneDup = $errDBEmailDup = $errDBOther = [];

        $user      = User::instance();
        $userAuth  = UserAuth::instance();
        $importNum = 0;

        $sliceSize = 100;
        $count     = count($rows);
        $lastNum   = $count % $sliceSize;
        $times     = (int)($count / $sliceSize);
        if ($lastNum != 0)
        {
            $times++;
        }

        //分批次写入数据
        for ($i = 0; $i < $times; $i++)
        {
            $length = $sliceSize;
            if ($lastNum && ($i == $times - 1))
            {
                $length = $lastNum;
            }
            $offset    = $i * $sliceSize;
            $sliceRows = array_slice($rows, $offset, $length, true);

            Db::startTrans();
            try
            {
                foreach ($sliceRows as $rowNum => $row)
                {
                    $name     = $row[0];
                    $fullName = $row[1];
                    $phone    = $row[2];
                    $email    = $row[3];
                    $sex      = $row[4];
                    $company  = $row[5];

                    try
                    {
                        do
                        {
                            $break = false;
                            if (($phone != '') && $userAuth->CheckExist_Phone($phone))
                            {
                                $errDBPhoneDup[] = $rowNum + 2;
                                $break           = true;
                            }
                            if (($email != '') && $userAuth->CheckExist_Email($email))
                            {
                                $errDBEmailDup[] = $rowNum + 2;
                                $break           = true;
                            }
                            //检查用户名,手机号,邮箱是否存在
                            if ($user->CheckExist($name))
                            {
                                $errDBNameDup[] = $rowNum + 2;
                                $break          = true;
                            }
                            if ($break)
                            {
                                break;
                            }
                            $importNum++;

                            //写入数据
                            $userAuth->Add($name, $name . DEF_USER_PWD, $phone, $email);
                            if ($name != USER_NAME_ADMIN)
                            {
                                //2.0 设置角色为普通用户
                                (UserRoles::instance())->ModifyRoleByUser($name, [ROLE_USER_ROLE]);
                            }
                            $user->Add($name, null, $fullName, $sex, '', 0, $company);

                        } while (0);
                    }
                    catch (\Exception $e)
                    {
                        if (strpos($e->getMessage(), $name))
                        {
                            $errDBNameDup[] = $rowNum + 2;
                        }
                        else if (strpos($e->getMessage(), $phone))
                        {
                            $errDBPhoneDup[] = $rowNum + 2;
                        }
                        else if (strpos($e->getMessage(), $email))
                        {
                            $errDBEmailDup[] = $rowNum + 2;
                        }
                        else
                        {
                            $errDBOther[] = $rowNum + 2;
                        }
                    }
                }

                //4.0 正式入库
                Db::commit();
            }
            catch (\Throwable $e)
            {
                //取消入库
                Db::rollback();

                self::$_error_code = $e->getCode();
                self::$_error_msg  = $e->getMessage();

                return false;
            }
        }

        //统计信息


        $errDB['errDBNameDup']  = $errDBNameDup;
        $errDB['errDBPhoneDup'] = $errDBPhoneDup;
        $errDB['errDBEmailDup'] = $errDBEmailDup;
        $errDB['errDBOther']    = $errDBOther;

        return $errDB;
    }
}