<?php
namespace core;
class core
{
    public static $classMap = array();

    public static function run()
    {
        $route = new \core\lib\route();
        $ctrl = $route->ctrl;
        $action = $route->action;
        $file = XXXPHP . '/app/controller/' . $ctrl . '.php';
        $strClass = '\\app\controller\\' . $ctrl;
        if (is_file($file)) {
            include $file;
            $class = new $strClass();
            $class->$action();
        } else {
            throw new \Exception('找不到控制器' . $ctrl);
        }
    }

    //自动加载类
    public static function load($class)
    {
        $class = str_replace('\\', '/', $class);
        $classMap = self::$classMap;
        if (isset($classMap[$class])) {
            return true;
        }
        $file = XXXPHP . '/' . $class . '.php';
        if (is_file($file)) {
            include $file;
            self::$classMap[$class] = $class;
        } else {
            return false;
        }
    }
}