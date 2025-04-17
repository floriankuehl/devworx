<?php

namespace Devworx\Utility;

class GeneralUtility {
  
  /**
   * Reads data from the $_GET array with a fallback value
   *
   * @param string $key The key to read. if null, the $_GET array is returned
   * @param string $default The fallback value
   * @return mixed
   */
  static function Get(string $key=null,string $default=null){
    return ArrayUtility::key($_GET,$key,$default);
  }
  
  /**
   * Reads data from the $_POST array with a fallback value
   *
   * @param string $key The key to read. if null, the $_POST array is returned
   * @param string $default The fallback value
   * @return mixed
   */
  static function Post(string $key=null,string $default=null){
    return ArrayUtility::key($_POST,$key,$default);
  }
  
  /**
   * Reads data from the $_REQUEST array with a fallback value
   *
   * @param string $key The key to read. if null, the $_REQUEST array is returned
   * @param string $default The fallback value
   * @return mixed
   */
  static function Request(string $key=null,string $default=null){
    return ArrayUtility::key($_REQUEST,$key,$default);
  }
  
  /**
   * Reads data from the $_COOKIE array with a fallback value
   *
   * @param string $key The key to read. if null, the $_COOKIE array is returned
   * @param string $default The fallback value
   * @return mixed
   */
  static function Cookie(string $key=null,string $default=null){
    return ArrayUtility::key($_COOKIE,$key,$default);
  }
  
  /**
   * Reads data from the $_SESSION array with a fallback value
   *
   * @param string $key The key to read. if null, the $_SESSION array is returned
   * @param string $default The fallback value
   * @return mixed
   */
  static function Session(string $key=null,string $default=null){
    return ArrayUtility::key($_SESSION,$key,$default);
  }
  
  /**
   * Redirects the current location by replacing the Location header
   *
   * @param string $url The new location
   * @param int $status The HTTP status (defaults to 301)
   * @return void
   */
  static function redirect(string $url,int $status=301): void {
    header("Location: {$url}",true,$status);
  }
  
  /**
   * Creates an instance of a given class name
   *
   * @param string $className The FQCN for the new instance
   * @param array $args The arguments for the constructor
   * @return object|null
   */
  static function makeInstance(string $className,...$args): ?object{
    return class_exists($className) ? new $className(...$args) : null;
  }
}
