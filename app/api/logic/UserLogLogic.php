<?php
namespace app\api\logic;
use string\P4String;

use app\api\model\
{
    log\LogDetails, log\Logs, log\UserLogs, rbac\User
};

/**
 * 用户操作日志
 */
class UserLogLogic extends BaseLogic
{
    /**
     *  用户操作日志列表
     *
     * @param string|null $user_name
     * @param int|null    $op_type
     * @param string|null $st
     * @param string|null $et
     * @param int         $page
     * @param int         $pageSize
     *
     * @return array|bool
     */
    public function GetList(string $user_name=null, int $op_type=null, string $st = null, string $et = null, string $order_field = null, int $is_asc = YES,
        int $page = DEF_PAGE, int $pageSize = DEF_PAGE_SIZE)
    {
        try
        {
            $user = UserLogs::instance();
            $ops  = $user->GetList($user_name, $op_type, $st, $et, $order_field, $is_asc, $page, $pageSize);

            return $ops;
        }
        catch(\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();
            return false;
        }
    }

    /**
     * 添加日志
     *
     * @param        $userName
     * @param int    $op_type
     * @param string $op
     * @param string $url
     * @param string $params
     * @param int    $time
     * @param int    $userIo
     * @param string $useMem
     *
     * @return bool|string
     */
    public function Add(
        string $opId, string $op = '', int $op_type = LOGOP_OP_TYPE_ADD, string $url = '',
        string $params = '', int $opResult = 0, string $opCommnt = '', int $time = 0, int $userIo = 0, string $useMem = '')
    {
        return Logs::instance()->Add($opId, self::$_uname, $op_type, $op, $url, $params, $opResult, $opCommnt,
            $time, $userIo, $useMem);
    }

    /**
     * 用户操作日志列表
     *
     * @param string|null $userNameKey
     * @param int|null    $op_type
     * @param int|null    $st
     * @param int|null    $et
     * @param int         $page
     * @param int         $pageSize
     *
     * @return array|false
     */
    public function Get_List(
        string $userNameKey = null, int $op_type = null, string $st = null, string $et = null,
        string $fullNameKey = null, string $order_field = null, int $is_asc = YES, int $page = DEF_PAGE, int $pageSize = DEF_PAGE_SIZE)
    {
        try
        {
            $log = Logs::instance();
            $ops = $log->GetList($userNameKey, $op_type, $st, $et, $fullNameKey, $order_field, $is_asc, $page, $pageSize);

            if ($ops[1] > 0)
            {
                $user = User::instance();
                foreach ($ops[0] as &$op)
                {
                    $op['full_name'] = $user->GetFullName($op['user_name']);
                }
            }

            return $ops;
        }
        catch (\Throwable $e)
        {
            static::$_error_code = \EC::DB_OPERATION_ERROR;
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    /**
     * 获取详情
     *
     * @param string $op_id
     *
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function Info(string $op_id)
    {
        $log  = Logs::instance();
        $info = $log->GetInfo($op_id);
        if (!$info)
        {
            static::$_error_code = \EC::DB_RECORD_NOTEXIST;

            return false;
        }

        $logDetail = LogDetails::instance();
        $opdIds    = $logDetail->GetOpdIds($op_id);

        $info['opd_list'] = [];
        if ($opdIds)
        {
            foreach ($opdIds as $opdId)
            {
                $info['opd_list'][] = $logDetail->GetDiff($opdId);
            }
        }

        $user              = User::instance();
        $info['op_params'] = P4String::jsondecode($info['op_params']);
        $info['full_name'] = $user->GetFullName($info['user_name']);

        return $info;
    }
}