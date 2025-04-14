<?php
namespace Devworx;

class ConfigManager {

  static $config = null;

  public static function loadConfig(string $fileName): bool {
    if( is_file($fileName) ){
      self::$config = json_decode( file_get_contents( $fileName ), true );
      return is_array(self::$config);
    }
    return false;
  }
  
  public static function saveConfig(string $fileName,bool $overwrite=true): bool {
    if( is_file($fileName) && !$overwrite )
      return false;
    $content = json_encode( self::$config, JSON_PRETTY_PRINT );
    return file_put_contents($fileName,$content);
  }

  public static function getConfig(...$path): string|array|int|float|null {
    $result = self::$config;
    forEach( $path as $key ){
      if( array_key_exists($key,$result) ){
        $result = $result[$key];
        continue;
      }
      $result = null;
      break;
    }
    return $result;
  }
  
  public static function setConfig(array $branch, $value, ...$path): bool {
    $pointer = ( 
      is_null($branch) ? 
      self::$config : 
      $branch 
    );
    $key = array_shift($path);
    
    if( empty($key) )
      return false;
    
    if( array_key_exists($key,$pointer) ){
      if( empty($path) ){
        $pointer[$key] = $value;
        return true;
      }
      return self::setConfig($pointer[$key], $value, ...$path);
    }
    
    if( empty($path) ){
      $pointer[$key] = $value;
      return true;
    }
    
    $pointer[$key] = [];
    
    return self::setConfig($pointer,$value,...$path);
  }
}
?>