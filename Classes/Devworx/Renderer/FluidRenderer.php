<?php

namespace Devworx\Renderer;

class FluidRenderer extends AbstractRenderer {
  
  /**
   * Parses a branch in the object tree
   *
   * @param string $key The key to read
   * @param mixed $branch The current branch
   * @return mixed
   */
  public static function parseBranch(string $key,$branch){
    
    if( empty($key) || is_null($branch) ) 
      return '';
    
    $segments = explode('.',$key);
    $first = array_shift($segments);
    
    if( empty($first) ) 
      return $branch;
    
    if( empty($segments) ){
      if( is_string($branch) && is_numeric($first) )
        return $branch[$first];
      if( is_object($branch) )
        return call_user_func([$branch,"get".ucfirst($first)]);
      if( is_array($branch) && array_key_exists($first,$branch) )
        return $branch[$first];
      return "{{$key}}";
    }
    
    $rest = implode('.',$segments);
    if( is_object($branch) )
      return self::parseBranch($rest,call_user_func([$branch,"get".ucfirst($first)]));
    if( is_array($branch) ){
      if( array_key_exists($key,$branch) )
        return $branch[$key];
      if( array_key_exists($first,$branch) )
        return self::parseBranch($rest,$branch[$first]);
      return "{{$first}.{$rest}}";
    }
    if( is_numeric($branch) )
      return $branch;
    if( is_string($branch) )
      return self::parseBranch($rest,$branch[$first]);
    
    return $branch;
  }
  
  /**
   * Extracts all variables from a given source string
   *
   * @param string $source The source text
   * @return array|null
   */
  public static function extractVariables(string $source): ?array {
    $matches = [];
    $found = preg_match_all('~\{([^{:}]*)\}~',$source,$matches,PREG_SET_ORDER,0);
    return $found === false ? null : array_column($matches,1);
  }
  
  /**
   * Converts a given value to a string
   *
   * @param string $value The given value
   * @param string $key A possible subkey of the value (not used)
   * @return string
   */
  public static function stringify( $value, string $key='' ): string {
    if( is_null($value) )
      return 'null';
    
    if( $value instanceof \DateTime )
      return $value->format('Y-m-d\TH:i:s');
    
    if( is_bool($value) )
      return $value ? '1' : '0';
    
    return $value;
  }
  
  /**
   * Renders a source string template using branched object access
   *
   * @param mixed $source The given source template text
   * @param array $variables The provided variables for this renderer
   * @param string $encoding The standard encoding for this renderer
   * @return mixed
   */
  public static function render($source,array $variables,string $encoding=''){
    if( is_string($source) ){
      $keys = self::extractVariables($source);
      if( is_null($keys) ) return $source;
      
      $values = [];
      foreach( $keys as $i => $key ){
        $value = self::parseBranch($key,$variables);
        $values["{{$key}}"] = self::stringify($value,$key);
      }
      
      return str_replace(
        array_keys($values), 
        array_values($values),
        $source
      );
    }
    return $source;
  }
}

?>
