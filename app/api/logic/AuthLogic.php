<?php

namespace app\api\logic;

use app\api\model\
{
    rbac\Role, rbac\UserRoles, rbac\RoleRules, rbac\Rule
};

use think\facade\Db;

/**
 * 权限 rule 类
 */
class AuthLogic extends BaseLogic
{
    // +----------------------------------------------------------------------------------------------------------------
    // | API 调用授权检测
    // +----------------------------------------------------------------------------------------------------------------
    /**
     * 是否是白名单的 API
     *
     * @param array $while_list 白名单列表（控制层的API）
     *
     * @return bool
     */
    public function IsWhite(array $while_list)
    {
        $ruleName = MODULE_NAME . '-' . CONTROLLER_NAME . '.' . ACTION_NAME;
        if (in_array($ruleName, $while_list))
        {
            return true;
        }

        return false;
    }

    /**
     * 检测是否有该API的调用权限
     * Todo: 考虑使用缓存!!!
     *
     * @param array $while_list
     *
     * @return bool
     */
    public function Check(array $while_list)
    {
        //特例处理
        //【1.0】是否是超级管理员
        if ($this->IsAdmin())
        {
            return true;
        }

        //【2.0】白名单
        $ruleName = MODULE_NAME . '-' . CONTROLLER_NAME . '.' . ACTION_NAME;
        if (in_array($ruleName, $while_list))
        {
            return true;
        }

        //【3.0】拥有权限用户
        $ur    = UserRoles::instance();
        $rr    = RoleRules::instance();
        $r     = Rule::instance();
        $roles = [];

        //根据用户获取角色列表
        if ($userName = self::$_uname)
        {
            $roles = $ur->GetRoles($userName);
        }

        //添加用户角色
        if (!$roles)
        {
            $roles[] = ROLE_GUEST_ROLE;
        }

        //根据角色获取权限列表
        $rules = $rr->GetRulesByRoles($roles);

        //根据权限列表获取权限内容
        $ruleList = [];
        foreach ($rules as $rule_id)
        {
            $info = $r->GetInfo($rule_id);
            unset($info['user_name']);

            $ruleList[] = $info;
        }

        //根据权限列表和模块,控制器,方法 判断是否具有权限
        $rule = [
            'module'  => MODULE_NAME,
            'control' => CONTROLLER_NAME,
            'method'  => ACTION_NAME,
        ];
        if (in_array($rule, $ruleList))
        {
            return true;
        }

        //需要登录
        if (!self::$_uname)
        {
            static::$_error_code = \EC::URL_ACCESSTOKEN_NOTEXIST_ERROR;
            return false;
        }

        static::$_error_code = \EC::PERMISSION_NO_ERROR;

        return false;
    }

    // +----------------------------------------------------------------------------------------------------------------
    // | 权限
    // +----------------------------------------------------------------------------------------------------------------
    /**
     * 获取权限列表
     *
     * @param array       $white_list
     * @param string      $role_id
     * @param string|null $module
     *
     * @return array | bool
     */
    public function GetList(array $white_list, string $role_id, string $module = null, string $order_field = null, int $is_asc = YES)
    {
        //1.0 角色是否存在
        $info = Role::instance()->GetInfo($role_id);
        if (!$info)
        {
            static::$_error_code = \EC::ROLE_NOTEXIST_ERROR;

            return false;
        }

        $ru = Rule::instance();

        //1.1 获取所有数据
        $listAll           = $ru->GetList($module, $order_field, $is_asc);
        $hasPermissionList = RoleRules::instance()->GetRulesByRoles([$role_id]);

        //当有权限时，标记可用，否则标记不可用
        $listAll = array_map(function (&$item) use ($hasPermissionList) {
            $item['status'] = in_array($item['rule_id'], $hasPermissionList) ? YES : NO;

            return $item;
        }, $listAll);

        //2.0 过滤白名单
        foreach ($listAll as $key => $item)
        {
            $ruleName = strtolower($item['module'] . '-' . $item['control'] . '.' . $item['method']);
            if (in_array($ruleName, $white_list))
            {
                unset($listAll[$key]);
            }
        }

        //3.0 整理数据 +  汉化
        $lang = include_once(CONF_PATH . 'rule_zh.php');

        $res = [];
        foreach ($listAll as $rule)
        {
            $control = $rule['module'] . '-' . $rule['control'];

            $method          = $rule['method'];
            $res[$control][] = [
                'id'     => $rule['rule_id'],
                'method' => $method,
                'status' => $rule['status'],
                'zh'     => $lang[$control][$method] ?? $method,
            ];
        }

        return $res;
    }

    /**
     * 设置权限规则
     *
     * @param string      $role_id
     * @param string|null $rules
     * @param string|null $module
     *
     * @return bool
     */
    public function SetRules(string $role_id, string $rules = null, string $module = null)
    {
        //1.0 角色是否存在
        $info = Role::instance()->GetInfo($role_id);
        if (!$info)
        {
            static::$_error_code = \EC::ROLE_NOTEXIST_ERROR;

            return false;
        }

        if ( $role_id == ROLE_USER_ROLE || $role_id == ROLE_TEACHER_ROLE)
        {
            if (self::$_uname != USER_NAME_ADMIN)
            {
                static::$_error_code = \EC::PERMISSION_NO_ERROR;

                return false;
            }
        }


        //2.0 修改角色对应的权限列表
        $rules = ids2array($rules);
        $rr    = RoleRules::instance();

        Db::startTrans();
        try
        {
            $rtn = $rr->ModifyByRules(self::$_uname, $role_id, $rules, $module);

            Db::commit();
        }
        catch (\Throwable $e)
        {
            Db::rollback();

            static::$_error_code = \EC::DB_OPERATION_ERROR;
            return false;
        }

        return $rtn;
    }

    /**
     * 同步规则表 -- 用于后台调用（刷新）
     *
     * @param array $currentRules 当前存在的规则的列表
     */
    public function ReNewRules(array $currentRules)
    {
        //已有规则的列表
        $ruleCls  = Rule::instance();
        $ruleList = $ruleCls->GetList();

        //获取删除、新增权限列表
        $addS = [];
        $delS = [];

        if (empty($ruleList))
        {
            $addS = $currentRules;
            $delS = [];
        }
        else
        {
            //adds
            foreach ($currentRules as $curKey => $curRule)
            {
                $bExist = false;

                foreach ($ruleList as $key => $rule)
                {
                    if (($curRule['module'] == $rule['module']) && ($curRule['control'] == $rule['control']) &&
                        ($curRule['method'] == $rule['method']))
                    {
                        $bExist = true;
                        unset($currentRules[$curKey]);
                        unset($ruleList[$key]);
                        continue;
                    }
                }

                if (!$bExist)
                    $addS[] = $curRule;
            }

            //dels
            foreach ($ruleList as $key => $rule)
            {
                $bExist = false;

                foreach ($currentRules as $curKey => $curRule)
                {
                    if (($curRule['module'] == $rule['module']) && ($curRule['control'] == $rule['control']) &&
                        ($curRule['method'] == $rule['method']))
                    {
                        $bExist = true;
                        continue;
                    }
                }

                if (!$bExist)
                    $delS[] = $rule;
            }
        }

        if (!empty($addS))
        {
            $addS = array_map(function ($item) {
                return array_merge($item, [
                    'rule_id'   => guid(),
                    'user_name' => self::$_uname
                ]);
            }, $addS);
        }

        //失效规则删除
        if (!empty($delS))
        {
            foreach ($delS as &$rule)
            {
                $rule = $rule['rule_id'];
            }
        }

        try
        {
            //新规则入库
            $ruleCls->AddAll($addS);

            //移除规则
            $ruleCls->DelByRuleIds($delS);

            //删除失效的映射
            $rr = new RoleRules();
            $rr->DelByRules($delS);
        }
        catch (\Throwable $e)
        {
            static::$_error_code = $e->getCode();
            static::$_error_msg  = $e->getMessage();

            return false;
        }
    }

    // +----------------------------------------------------------------------------------------------------------------
    // | 栏目 / 列表
    // +----------------------------------------------------------------------------------------------------------------
    /**
     * 获取权限 -- 显示/隐藏 列表
     */
    public function ViewList()
    {
        //1.0 获取角色列表
        if (self::$_uname == USER_NAME_ADMIN)
        {
            $roles = null;
        }
        else
        {
            $ur = UserRoles::instance();
            //返回值是role_id数组
            $roles = $ur->GetRoles(self::$_uname);
        }

        //1.1 获取权限列表
        $ru   = Rule::instance();
        $list = $ru->GetViewRules($roles);

        //汉化
        $lang     = include_once(CONF_PATH . 'rule_zh.php');
        $viewList = [];
        foreach ($list as $k => $item)
        {
            $viewList[] = [
                'c'  => $item['c'],
                'm'  => $item['m'],
                'zh' => $lang[$item['c']][0] ?? $item['c'],
            ];
        }

        return $viewList;
    }
}