<?php
namespace app\api\controller\auth;

use app\api\controller\BaseController;
use app\api\logic\
{
    UserRoleLogic
};

/**
 * 用户角色管理
 */
class UserRoleController extends BaseController
{
    public function Index()
    {
        return $this->R();
    }

    /**
     * 修改用户角色
     */
    public function Modify()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        $param = $this->I([
            [
                'role_ids',
                null,
                's',
                'require'
            ],
            [
                'user_name',
                null,
                's',
                'require|length:1,20'
            ],
        ]);

        (new UserRoleLogic())->Modify($param['user_name'], $param['role_ids']);

        return $this->R();
    }

    /**
     * 用户列表
     */
    public function Get_List()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        $param = $this->I([
            [
                'role_id',
                null,
                's',
                'length:32'
            ],
            [
                'user_name_key',
                null,
                's',
                'length:1,20'
            ],
            [
                'nick_name_key',
                null,
                's',
                'length:1,20'
            ],
            [
                'full_name_key',
                null,
                's',
                'length:1,20'
            ],
            [
                'user_type',
                null,
                'd',
                //  'number|in:' . USER_TYPE_PROVIDER . ',' . USER_TYPE_GENERAL,
            ],
            [
                'all',
                NO,
                'd',
                'number|in:' . YES . ',' . NO,
            ],
            [
                'order_field',
                'create_time',
                's',
                'length:1,20'
            ],
            [
                'is_asc',
                 YES,
                'd',
                'in:' . YES . ',' . NO
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
                'between:1,' . DEF_PAGE_MAXSIZE
            ]
        ]);

        $users = (new UserRoleLogic())->GetList($param['role_id'], $param['user_name_key'], $param['nick_name_key'],
            $param['full_name_key'], $param['user_type'], $param['all'] == YES, $param['order_field'], $param['is_asc'], $param['page'], $param['per_page']);

        if ($users)
            return $this->R(null, null, $users[0], $users[1]);

        return $this->R();
    }
}