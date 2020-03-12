<?php
namespace app\api\controller\traits;

/**
 * 服务层接口反射
 */
trait Reflection
{
    /**
     * 获取每个控制器下的自定义public方法
     *
     * @param array       $hiddenController
     *
     * @return array
     *   module  = "api"
     *   control = "auth.role"
     *   method  = "index"
     *
     * @throws \ReflectionException
     */
    protected function GetPublicMethods(array $hiddenController = self::HIDDEN_CONTROLLER)
    {
        //从配置文件获取控制器后缀
        $suffix = '';
        if (config('route.controller_suffix'))
        {
            $suffix = ucfirst(config('route.controller_layer'));
        }

        $methods = [];
        foreach (self::MODULES as $moduleName => $graded_names)
        {
            foreach ($graded_names as $graded => $controls)
            {
                foreach ($controls as $control)
                {
                    //过滤掉Error控制器
                    if (in_array($control, $hiddenController))
                    {
                        continue;
                    }

                    $controllerFile = base_path() . "$moduleName/controller/$graded/" . $control . "$suffix" . '.php';

                    //控制器名称
                    $controller = basename($controllerFile, '.php');

                    //此控制器下的所有方法
                    $controlMethods = "app\\$moduleName\\controller\\$graded\\" . $controller;
                    $methodTemps    = get_class_methods($controlMethods);
                    if (!$methodTemps)
                    {
                        continue;
                    }

                    //过滤方法
                    //过滤非自定义方法
                    $methodTemps = array_diff($methodTemps, [
                        'initialize',
                        '_empty',
                        '__construct',
                        '__debugInfo',
                        'registerMiddleware',
                        'home_index'
                    ]);

                    //过滤非public方法
                    foreach ($methodTemps as $k => $method)
                    {
                        $foo = new \ReflectionMethod("app\\$moduleName\\controller\\$graded\\" . $controller, $method);
                        foreach (\Reflection::getModifierNames($foo->getModifiers()) as $modifierName)
                        {
                            if (in_array($modifierName, [
                                'protected',
                                'private',
                            ]))
                            {
                                unset($methodTemps[$k]);
                                break;
                            }
                        }
                    }
                    if (empty($methodTemps))
                    {
                        continue;
                    }

                    $controlName = $graded . '.' . strtolower($control);
                    $methods =  array_merge($methods, array_map(function ($item) use ($moduleName, $controlName) {
                        return [
                            'module'  => $moduleName,
                            'control' => $controlName,
                            'method'  => strtolower($item)

                        ];
                    }, $methodTemps));
                }
            }
        }

        return $methods;
    }
}