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

        $vali = $this->I([
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
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        (new UserRoleLogic())->Modify(self::$_input['user_name'], self::$_input['role_ids']);

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

        $vali = $this->I([
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
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $users = (new UserRoleLogic())->GetList(self::$_input['role_id'], self::$_input['user_name_key'], self::$_input['nick_name_key'],
            self::$_input['full_name_key'], self::$_input['user_type'], self::$_input['all'] == YES, self::$_input['order_field'], self::$_input['is_asc'], self::$_input['page'], self::$_input['per_page']);

        if ($users)
            return $this->R(null, null, $users[0], $users[1]);

        return $this->R();
    }
}