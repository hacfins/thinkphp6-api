<?php
namespace app\api\controller\user;

use app\api\controller\BaseController;
use app\api\logic\
{
    UserLogLogic
};

/**
 * 用户操作日志
 */
class UserLogController extends BaseController
{
    public function Index()
    {
        return $this->R();
    }

    public function Index_User()
    {
        return $this->R();
    }

    /**
     * 操作日志列表
     */
    public function GetList()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //数据接收
        $vali = $this->I([
            [
                'st',
                null,
                's',
                'dateFormat:Y-m-d H:i:s'
            ],
            [
                'et',
                null,
                's',
                'dateFormat:Y-m-d H:i:s'
            ],
            [
                'order_field',
                'create_time',
                's',
                'length:1,20'
            ],
            [
                'is_asc',
                NO,
                'd',
                'in:' . YES .','. NO
            ],
            [
                'page',
                DEF_PAGE,
                'd',
                'number|>:0'
            ],
            [
                'per_page',
                DEF_PAGE_SIZE,
                'd',
                'between:1,50'
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        list($list, $count) = (new UserLogLogic())->GetList(self::$_uname, null, self::$_input['st'], self::$_input['et'], self::$_input['order_field'], self::$_input['is_asc'],
            self::$_input['page'], self::$_input['per_page']);

        if($count > 0)
        {
            foreach ($list as &$item)
            {
                unset($item['user_name']);
            }
        }

        return $this->R(null, null, $list, $count);
    }

    /**
     * 操作日志列表
     */
    public function Get_List_Admin()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //数据接收
        $vali = $this->I([
            [
                'st',
                null,
                's',
                'dateFormat:Y-m-d H:i:s'
            ],
            [
                'et',
                null,
                's',
                'dateFormat:Y-m-d H:i:s'
            ],
            [
                'user_name_key',
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
                'order_field',
                'create_time',
                's',
                'length:1,20'
            ],
            [
                'is_asc',
                NO,
                'd',
                'in:' . YES .','. NO
            ],
            [
                'page',
                DEF_PAGE,
                'd',
                'number|>:0'
            ],
            [
                'per_page',
                DEF_PAGE_SIZE,
                'd',
                'between:1,50'
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        list($list, $count) = (new UserLogLogic())->Get_List(self::$_input['user_name_key'], null, self::$_input['st'], self::$_input['et'],
            self::$_input['full_name_key'], self::$_input['order_field'], self::$_input['is_asc'], self::$_input['page'], self::$_input['per_page']);

        return $this->R(null, null, $list, $count);
    }

    /**
     * 获取日志详情
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function Info()
    {
        if(!$this->NeedToken())
        {
            return $this->R();
        }

        //**数据接收**
        $vali = $this->I([
            [
                'op_id',
                null,
                's',
                'require|length:32'
            ]
        ]);
        if ($vali !== true)
        {
            return $this->R(\EC::PARAM_ERROR, null, $vali);
        }

        $info = (new UserLogLogic())->Info(self::$_input['op_id']);

        //**数据返回**
        if ($info)
            return $this->R(null, null, $info);

        return $this->R();
    }
}