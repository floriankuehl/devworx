<?php

namespace Devworx\Renderer;

use Devworx\Frontend;
use Devworx\Utility\ArrayUtility;

class JsonRenderer {
  
  public static function getFlags(): int {
    return 0;
  }
  
  public static function render($source,array $variables,string $encoding=''){
    header("Content-Type: application/json;charset=utf-8");
    $fn = empty($encoding) ? $encoding : "{$encoding}_encode";
    
    if( is_array($source) ){
      $result = [];
      foreach( $source as $key => $value ){
        if( is_array($value) ){
          $result[$key] = ArrayUtility::keys($variables,...$value);
          continue;
        }
        $result[$key] = ArrayUtility::key($variables,$value,NULL);
      }
      return empty($fn) ? $result : call_user_func($fn,$result,self::getFlags());
    }
    
    return empty($fn) ? $variables : call_user_func($fn,$variables,self::getFlags());
  }
  
}

?>