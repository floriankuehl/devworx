<?php

namespace Devworx\Utility;

class ArrayUtility {
  
  static function has(array $array,string $key){
    return array_key_exists($key,$array);
  }
  
  static function remove(array &$array,...$keys){
    foreach( $keys as $i => $key ){
      if( array_key_exists($key,$array) )
        unset($array[$key]);
    }
    return $array;
  }
  
  static function key(array $array,string $key=null,$default=null){
    if( is_null($key) )
      return $array;
    if( self::has($array,$key) )
      return $array[$key];
    return $default;
  }
  
  static function keys(array $array,...$keys){
    $current = $array;
    foreach($keys as $i=>$key){
      $current = self::key($current,$key,null);
      if( is_null($current) ) break;
    }
    return $current;
  }
  
  static function empty(array $array,$key=null): bool {
    if( is_null($key) )
      return empty($array);
    if( is_string($key) )
      return self::has($array,$key) ? empty($array[$key]) : true;
    if( is_array($key) ){
      $result = false;
      foreach( $key as $k ){
        $result = $result || self::empty($array,$k);
        if( $result ) break;
      }
      return $result;
    }
    return true;
  }
  
  static function hasValue(array $array,string $key){
    return self::has($array,$key) && !empty($array[$key]);
  }
  
  static function index(array $array,string $key,bool $group=false){
    $result = [];
    
    foreach( $array as $i => $row ){
      $k = $row[$key];
      if( $group ){
        if( array_key_exists($k,$result) )
          $result[$k][]= $row;
        else
          $result[$k] = [$row];
        continue;
      }
      $result[$k] = $row;
    }
    
    return $result;
  }
  
  static function combine(...$arrays){
    $result = [];
    
    foreach( $arrays as $i => $array ){
      if( is_array($array) && !empty($array) ){
        $result = array_reduce(
          array_keys($array),
          function($acc,$key) use ($array){
            $acc[$key] = $array[$key];
            return $acc;
          },
          $result
        );
      }
    }
    
    return $result;
  }
  
  static function isAssoc(array $array){
    return count( array_filter( array_keys($array), 'is_string' ) ) > 0;
  }
  
  static function filter(array $source,array $options){
    if( is_null($options) || empty($options) )
      return $source;
    
    $check = array_keys($options);
    $check = $check[0];
    if( array_key_exists($check,$source) ){
      $source = [$source];
    } 
    return array_filter($source,function($row) use ($options){
      $keep = true;
      foreach( $options as $k => $v ){
        $keep = array_key_exists($k,$row) && (
          is_array($options[$k]) ? 
            in_array($row[$k],$options[$k]) :
            $row[$k] == $options[$k]
          );
        if( !$keep ) break;
      }
      return $keep;
    });
  }
  
}