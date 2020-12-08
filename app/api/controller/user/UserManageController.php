<?php

namespace app\api\controller\user;

use app\api\controller\BaseController;
use app\api\logic\
{BaseLogic, UploadLogic, UserLogic};

/**
 * 用户管理
 */
class UserManageController extends BaseController
{
    public function Index()
    {
        return $this->R();
    }

    /**
     * 添加用户
     */
    public function Add()
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
                'require|length:4,20|alphaPrefix|alphaDash',
            ],
            [
                'full_name',
                '',
                's',
                'length:2,20',
            ],
            [
                'company',
                '',
                's',
                'length:1,40',
            ],
            [
                'sex',
                null,
                'd',
                'in:' . USER_SEX_MAN . ',' . USER_SEX_WOMEN . ',' . USER_SEX_UNKOWN,
            ],
            [
                'phone',
                null,
                's',
                'mobile',
            ],
        ]);

        $userName  = strtolower($param['user_name']);
        $full_name = $param['full_name'];
        $sex       = $param['sex'];
        $phone     = $param['phone'];
        $company   = $param['company'];

        (new UserLogic())->Add($userName, $full_name, $phone, $sex, $company);

        $info = [];
        if (BaseLogic::$_error_code == \EC::SUCCESS)
            $info = (new UserLogic())->GetInfo($userName);

        return $this->R(null, null, $info);
    }

    /**
     * 添加用户
     */
    public function Enabled()
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
                'require|length:4,200',
            ],
            [
                'enable',
                YES,
                'd',
                'in:' . YES . ',' . NO,
            ],
        ]);

        (new UserLogic())->Disable_Or_Enables(strtolower($param['user_name']), !($param['enable'] == YES));

        return $this->R();
    }

    /**
     * 用户列表
     */
    public function GetList()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //数据接收
        $param = $this->I([
            [
                'user_name_key',
                null,
                's',
                'length:1,20',
            ],
            [
                'full_name_key',
                null,
                's',
                'length:1,20',
            ],
            [
                'phone_key',
                null,
                's',
            ],
            [
                'sex',
                null,
                'd',
                'in:' . USER_SEX_MAN . ',' . USER_SEX_WOMEN . ',' . USER_SEX_UNKOWN,
            ],
            [
                'st',
                null,
                's',
                'dateFormat:Y-m-d H:i:s',
            ],
            [
                'et',
                null,
                's',
                'dateFormat:Y-m-d H:i:s',
            ],
            [
                'order_field',
                'create_time',
                's',
                'length:1,20',
            ],
            [
                'is_asc',
                YES,
                'd',
                'in:' . YES . ',' . NO,
            ],
            [
                'page',
                DEF_PAGE,
                'd',
                'number|>:0',
            ],
            [
                'per_page',
                DEF_PAGE_SIZE,
                'd',
                'between:1,50',
            ],
        ]);

        list($list, $count) = (new UserLogic())->GetList($param['user_name_key'],
            $param['full_name_key'], $param['phone_key'], $param['sex'],
            $param['st'], $param['et'], true, $param['order_field'], $param['is_asc'], $param['page'], $param['per_page']);

        return $this->R(null, null, $list, $count);
    }

    // +--------------------------------------------------------------------------
    // |  用户导入
    // +--------------------------------------------------------------------------
    /**
     * 批量导入-下载批量导入模板
     */
    public function Batch_Download()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //**参数接收**
        $param = $this->I([
            [
                'check',
                YES,
                'd',
                'in:' . YES . ',' . NO,
            ],
        ]);

        $check = $param['check'];
        $rtn   = (new UserLogic())->BatchModel($param['check']);

        if ($check == YES)
            return $this->R(null, null, ['exist' => $rtn]);

        return $rtn;
    }

    /**
     * 批量导入-上传表格
     */
    public function Batch_Import()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //**参数接收**
        $param = $this->I([
            [
                'type',
                YES,
                'd',
                'in:' . YES . ',' . NO,
            ],
        ]);

        @set_time_limit(60 * 30);// 执行时间延长

        //接收上传的文件存入临时文件目录下
        $fid        = filename_microtime();
        $fileSize   = 1024 * 1024 * 2;
        $sourceFile = UploadLogic::UploadSimple($fid,
            ['file' => "fileSize:$fileSize|fileExt:xls,xlsx"],
            DIR_TEMPS_USERS);

        if (!$sourceFile)
        {
            return $this->R();
        }

        $rst = (new UserLogic())->BatchUpload($sourceFile, $param['type']);

        //**数据返回**
        return $this->R(null, null, $rst);
    }

    /**
     * 批量导入-下载批量导入用户的结果报表
     */
    public function Batch_Report()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //**参数接收**
        $param = $this->I([
            [
                'fid',
                null,
                's',
                'require|length:32',
            ],
            [
                'check',
                YES,
                'd',
                'in:' . YES . ',' . NO,
            ],
        ]);

        $check = $param['check'];
        $rtn   = (new UserLogic())->GetReport($param['fid'], $param['check']);

        if (YES == $check)
        {
            return $this->R(null, null, ['exist' => $rtn]);
        }
    }
}