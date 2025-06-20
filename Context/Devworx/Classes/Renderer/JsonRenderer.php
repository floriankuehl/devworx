<?php

namespace Devworx\Renderer;

use \Devworx\Frontend;
use \Devworx\Utility\ArrayUtility;

class JsonRenderer extends AbstractRenderer {
  
  /**
   * Renders a flag that will be added to the encoding function
   *
   * @return int
   */
  public static function getFlags(): int {
    return 0;
  }
  
  public function supports(\mixed $template): bool {
	return is_array($template) || is_object($template);
  }
  
  /**
   * Renders a given source with provided variables
   * If source is an array, the provided keys in variables are extracted from the source array.
   * If source is not an array, the variables are encoded
   *
   * @return mixed
   */
  public function render(\mixed $source,array $variables,string $encoding=''): string {
    header("Content-Type: application/{$encoding};charset=utf-8");
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
