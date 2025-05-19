<?php

namespace Devworx\Utility;

class StringUtility {
  
  /**
   * Cleans a string from non-alphanumeric characters
   *
   * @param string $value The given value
   * @return string
   */
  public static function cleanup(string $value): string {
    return preg_replace('/[^\da-z]/i', '', $value);
  }
  
  /**
   * Cleans a filename string from invalid characters
   *
   * @param string $value The given value
   * @return string
   */
  public static function cleanupFile(string $value): string {
	  return preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $value);
  }
  
  /**
   * Checks if the given string is a MD5 hash of 32B
   *
   * @param string $md5 The given value
   * @return bool
   */
  public static function isMd5(string $md5=''): bool {
    return (bool) preg_match('/^[a-f0-9]{32}$/', $md5);
  }
  
  /**
   * Checks if the given string is a Base64 string
   *
   * @param string $value The given value
   * @return bool
   */
  public static function isBase64(string $base64=''): bool {
    return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $base64);
  }
  
  /**
   * Strips a phone number of formatting characters (german)
   *
   * @param string $value The given value
   * @return string
   */
  public static function phone(string $value): string {
    $value = str_replace( 
      [' ','-','/','(0)'], 
      ['','','','0'],
      $value 
    );
    if( substr($value,0,1) == '0' )
      $value = '+49' . substr($value,1,$length-1);
    return $value;
  }
  
  /**
   * Builds a valid url from a given string
   *
   * @param string $value The given value
   * @return string
   */
  public static function url(string $value): string {
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
  
  /**
   * Converts \r\n and \n to PHP_EOL
   *
   * @param string $value The given value
   * @return string
   */
  public static function realNL(string $value): string {
	$value = str_replace('\r\n', PHP_EOL, $value);
	$value = str_replace('\n', PHP_EOL, $value);
	return str_replace('\"', '"', $value);
  }
}

?>
