<?php
namespace core;
class xbh{
    public static $classMap=[];

     public static function run(){
        $route=new \core\lib\route();
//        var_dump($route);
    }

    public static function load($class){
         if(isset($classMap[$class])) return true;
         str_replace('\\','/',$class);
        $file=XBH.'/'.$class.'.php';
         if(is_file($file)){
             include $file;
             self::$classMap[$class]=$class;
         }else{
             return false;
         }
    }
}