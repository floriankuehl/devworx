<?php

namespace Devworx\Utility;

class GeneralUtility {
    
  static function Get(string $key=null,string $default=null){
    return ArrayUtility::key($_GET,$key,$default);
  }
  
  static function Post(string $key=null,string $default=null){
    return ArrayUtility::key($_POST,$key,$default);
  }
  
  static function Request(string $key=null,string $default=null){
    return ArrayUtility::key($_REQUEST,$key,$default);
  }
  
  static function Cookie(string $key=null,string $default=null){
    return ArrayUtility::key($_COOKIE,$key,$default);
  }
  
  static function Session(string $key=null,string $default=null){
    return ArrayUtility::key($_SESSION,$key,$default);
  }
  
  static function redirect(string $url,int $status=301): void {
    header("Location: {$url}",true,$status);
  }
  
  static function makeInstance(string $className,...$args): ?object{
    return class_exists($className) ? new $className(...$args) : null;
  }
}