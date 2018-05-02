<?php
/**
 * Created by PhpStorm.
 * User: xiebh
 * Date: 2018/4/17
 * Time: 22:00
 */

namespace core\lib;
class route
{
    public $controller = '';
    public $action = "";

    public function __construct()
    {
        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/') {
            $path = $_SERVER['REQUEST_URI'];
            $patharr = explode('/', trim($path, '/'));//去除两边的/ 并且转化为数组
            if (isset($patharr[0])) $this->controller = $patharr[0];
            if (isset($patharr[1])) {
                $this->action = $patharr[1];
                unset($patharr[1]);
            } else {
                $this->action = 'index';
            }
            $count = count($patharr) + 2;
            $i = 2;
            while ($i < $count) {
                if (isset($patharr[$i + 1])) {
                    $_GET[$patharr[$i]] = $patharr[$i + 1];
                }
                $i = $i + 2;
            }
//            print_r($_GET);
        } else {
            $this->controller = 'index';
            $this->action = 'index';
        }
    }
}