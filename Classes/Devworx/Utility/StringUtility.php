<?php

namespace Devworx\Utility;

class StringUtility {
  
  public static function cleanup(string $value){
    return preg_replace('/[^\da-z]/i', '', $value);
  }
  
  public static function isMd5(string $md5=''): bool {
    return (bool) preg_match('/^[a-f0-9]{32}$/', $md5);
  }
  
  public static function isBase64(string $base64=''): bool {
    return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $base64);
  }
  
  public static function phone(string $value){
    $value = str_replace( 
      [' ','-','/','(0)'], 
      ['','','',''],
      $value 
    );
    if( substr($value,0,1) == '0' )
      $value = '+49' . substr($value,1,$length-1);
    return $value;
  }
  
  public static function url(string $value){
    $matches = [];
    if( preg_match("/http([s]?):\/\/([a-zA-Z\-\_\/\.]{1,})([\?]?[a-zA-Z\=\&\;]{1,})/m",$value,$matches) !== FALSE ){
      $value = $matches[2];
      $args = $matches[3];
      $value .= $args;
    }
    
    if( substr($value,-1) == '/' )
      $value = substr($value,0,strlen($value)-1);
    
    return "https://{$value}";
  }
}

?>