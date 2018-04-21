<?php
/**
 * Created by PhpStorm.
 * User: xiebh
 * Date: 2018/4/15
 * Time: 22:28
 */
define('XXXPHP',__DIR__);
define('CORE',XXXPHP.'/core');
  require CORE.'/core.php';
  spl_autoload_register('\core\core::load');
\core\core::run();

