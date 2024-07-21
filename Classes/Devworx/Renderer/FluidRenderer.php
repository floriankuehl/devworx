<?php

namespace Devworx\Renderer;

class FluidRenderer extends AbstractRenderer {
  
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
  
  public static function extractVariables(string $source): ?array {
    $matches = [];
    $found = preg_match_all('~\{([^{:}]*)\}~',$source,$matches,PREG_SET_ORDER,0);
    return $found === false ? null : array_column($matches,1);
  }
  
  public static function stringify( $value, string $key='' ): string {
    if( is_null($value) )
      return 'null';
    
    if( $value instanceof \DateTime )
      return $value->format('Y-m-d\TH:i:s');
    
    if( is_bool($value) )
      return $value ? '1' : '0';
    
    return $value;
  }
  
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

/*
  use \Devworx\Utility\DebugUtility;
  public static function extractViewHelper(string $source){
    $matches = [];
    $count = preg_match("/(\w{1,}):(\w{1,})\(([^()]*)\)/",$source,$matches);
    if( $count == false ) return $source;
    
    $args = array_map(
      function($a){
        $kv = explode(':',$a);
        return [
          trim($kv[0]),
          trim($kv[1])
        ];
      },
      explode(',',$matches[3])
    );
    
    return [
      'call' => $matches[0],
      'namespace' => $matches[1],
      'class' => ucfirst($matches[2])."ViewHelper",
      'args' => array_combine(
        array_column($args,0),
        array_column($args,1)
      ),
    ];
  }
  
  public static function callViewHelper(array $options){
    $namespace = $options['namespace'];
    $namespaces = \Frontend\Frontend::getConfig('namespaces');
    if( array_key_exists($namespace,$namespaces) ){
      $namespace = $namespaces[$namespace];
    }
    
    $className = $namespace . "\\" . $options['class'];
    if( class_exists($className) ){
      $viewHelper = new $className();
      if( $viewHelper instanceof \Devworx\Renderer\ViewHelper ){
        echo \Devworx\Utility\DebugUtility::var_dump([
          'instance' => $viewHelper,
          'options' => $options
        ]);
        return $viewHelper->process();
      }
    }
    return '';
  }
*/

?>