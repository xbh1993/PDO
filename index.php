<?php
/**
 * Created by PhpStorm.
 * User: xiebh
 * Date: 2018/4/15
 * Time: 22:28
 */
 require 'Db.php';

$str='123123';
 $config=['host'=>'localhost','user'=>'root','pass'=>'root','dbname'=>'test'];
 $db=new Db($config);
 $data=['title'=>'谢宝海','cid'=>12,'add_time'=>time()-100,'update_time'=>time()+500];
 $str=$db->where($where)->delete('user');
 var_dump($str);