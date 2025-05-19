<?php

namespace Devworx\Utility;

class ArrayUtility {
  
  /**
   * Checks for a key in a given array
   * 
   * @param array $array The given array
   * @param string $key The key to check in the given array
   * @return bool
   */
  static function has(array $array,string $key): bool {
    return array_key_exists($key,$array);
  }
  
  /**
   * Removes a list of keys from a given array
   * 
   * @param array $array The given array
   * @param array $keys The keys to remove
   * @return array
   */
  static function remove(array &$array,...$keys): array {
    foreach( $keys as $i => $key ){
      if( array_key_exists($key,$array) )
        unset($array[$key]);
    }
    return $array;
  }
  
  /**
   * Reads a key from an array with a default fallback
   * 
   * @param array $array The given array
   * @param string|null $key The key to read
   * @param mixed|null $default The fallback value
   * @return mixed
   */
  static function key(array $array,string $key=null,$default=null){
    if( is_null($key) )
      return $array;
    if( self::has($array,$key) )
      return $array[$key];
    return $default;
  }
  
  /**
   * Reads a keys path from a multidimensional array
   * 
   * @param array $array The given array
   * @param array $keys The key path to read
   * @return mixed
   */
  static function keys(array $array,...$keys){
    $current = $array;
    foreach($keys as $i=>$key){
      $current = self::key($current,$key,null);
      if( is_null($current) ) break;
    }
    return $current;
  }
  
  /**
   * Sets a key of a multidimensional array by a path
   * 
   * @param array $array The given array
   * @param mixed $value The value to set
   * @param array $path The path inside the array
   * @return bool
   */
  static function setKeys(array &$array, $value, ...$path): bool{
    $key = array_shift($path);
    if( empty($path) ){
      $array[$key] = $value;
      return self::has( $array, $key );
    }
    if( !( self::has($array,$key) && is_array($array[$key]) ) ){
      $array[$key] = [];
    }
    return self::setKeys($array[$key], $value, ...$path);
  }
  
  /**
   * Checks if a given array is empty
   * 
   * @param array $array The given array
   * @param null|string|array $key The optional key(s) to check inside the array, can be null, string or an array
   * @return bool
   */
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
  
  /**
   * Checks if a given array has a key with a value that is not empty
   * 
   * @param array $array The given array
   * @param string $key The key to check inside the array
   * @return bool
   */
  static function hasValue(array $array,string $key){
    return self::has($array,$key) && !empty($array[$key]);
  }
  
  /**
   * Builds an index from an array
   * 
   * @param array $array The given array
   * @param string $key The key to get the data from
   * @param bool $group Flag to group the retrieved values in a subarray
   * @return array
   */
  static function index(array $array,string $key,bool $group=false): array {
    $result = [];
    
    foreach( $array as $i => $row ){
      if( array_key_exists($key,$row) ){
        $k = $row[$key];
        if( $group ){
          if( array_key_exists($k,$result) )
            $result[$k][]= $row;
          else
            $result[$k] = [$row];
          continue;
        }
        $result[$k] = $row;
        continue;
      }
      throw new \Exception("Unknown array key '{$key}'");
    }
    
    return $result;
  }
  
  /**
   * Combines a list of arrays
   * 
   * @param array $arrays The given arrays
   * @return array
   */
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
  
  /**
   * Checks if an index in an array is valid
   * 
   * @param array $array The given array
   * @param int $index The given index
   * @return bool
   */
  static function isIndex(array $array,int $index): bool {
    return !( $index < 0 || $index >= count($array) );
  }
  
  /**
   * Checks if an array is associative
   * 
   * @param array $array The given array
   * @return bool
   */
  static function isAssoc(array $array): bool {
    return count( array_filter( array_keys($array), 'is_string' ) ) > 0;
  }
  
  /**
   * Filters values from an array
   * 
   * @param array $array The given array
   * @param array $filter The given filter
   * @return array
   */
  static function filter(array $source,array $filter){
    if( is_null($filter) || empty($filter) )
      return $source;
    
    $check = array_keys($filter);
    $check = $check[0];
    if( array_key_exists($check,$source) ){
      $source = [$source];
    } 
    return array_filter($source,function($row) use ($filter){
      $keep = true;
      foreach( $filter as $k => $v ){
        $keep = array_key_exists($k,$row) && (
          is_array($filter[$k]) ? 
            in_array($row[$k],$filter[$k]) :
            $row[$k] == $filter[$k]
          );
        if( !$keep ) break;
      }
      return $keep;
    });
  }
  
}
