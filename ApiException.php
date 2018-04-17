<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/17 0017
 * Time: 上午 11:12
 */
class ApiException extends  Exception{
   public $message='';
   public $httpCode=500;
   public $code='';
   public function __construct($msg="",$code=0,$httpCode=0)
   {
       $this->message=$msg;
       $this->code=$code;
       $this->httpCode=$httpCode;
   }
}