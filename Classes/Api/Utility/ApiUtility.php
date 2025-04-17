<?php

namespace Api\Utility;

use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\DebugUtility;

use \Devworx\Frontend;

class ApiUtility {
  
  public static $debug = !true;
  
  const ACCEPT_KEY = 'Accept';
  const CONTENT_TYPE_KEY = 'Content-Type';
  const CONTENT_TYPE = 'application/json';
  const CHARSET = 'utf-8';
  const HEADER_KEY = 'X-Devworx-Api';
  const CONTEXT_KEY = 'X-Devworx-Context';
  const CONTEXT = 'api';
  const SELFSIGNED = true;
  
  /** 
   * Checks if the HEADER_KEY is provided in the request header
   *
   * @return bool
   */
  public static function hasKey(): bool {
    return Frontend::hasHeader(self::HEADER_KEY);
  }
  
  /** 
   * Reads the HEADER_KEY provided in the request header 
   *
   * @return string|null
   */
  public static function getKey(): ?string {
    return ArrayUtility::key(Frontend::$header,self::HEADER_KEY);
  }
  
  /** 
   * Builds a header string by $key and $value
   *
   * @param string $key The header field
   * @param string $value The header field value
   * @return string
   */
  public static function getHeaderString(string $key,string $value): string {
    return "{$key}: {$value}";
  }
  
  /** 
   * Sets a header Key-Value-Pair
   *
   * @param string $key The header field
   * @param string $value The header field value
   * @return void
   */
  public static function setHeader(string $key,string $value): void {
    header(self::getHeaderString($key,$value));
  }
  
  /** 
   * Sets an array of headers
   *
   * @param array $headers The headers to set
   * @return void
   */
  public static function setHeaders(array $headers): void {
    foreach($headers as $key=>$value)
      self::setHeader($key,$value);
  }
  
  /** 
   * Initializes the API by setting neccessary headers
   *
   * @return void
   */
  public static function initialize(): void {
    self::setHeaders([
      self::ACCEPT_KEY => self::CONTENT_TYPE,
      self::CONTENT_TYPE_KEY => self::CONTENT_TYPE . ';charset=' . self::CHARSET,
      'Keep-Alive' => 'timeout=5, max=100',
    ]);
  }
  
  /** 
   * Builds a header array for the current user
   *
   * @return array
   */
  public static function getHeader(): array {
    return [
      self::getHeaderString(self::CONTENT_TYPE_KEY,self::CONTENT_TYPE . ';charset=' . self::CHARSET),
      self::getHeaderString(self::CONTEXT_KEY,self::CONTEXT),
      self::getHeaderString(self::HEADER_KEY,Frontend::getConfig('user','login')),
    ];
  }
  
  /** 
   * Builds a url for a given controller action pair with additional arguments
   *
   * @param string $controller The target controller
   * @param string $action The target controller action
   * @param array $arguments The additional arguments
   * @return string
   */
  public static function getUrl(string $controller,string $action,array $arguments=null): string {
    $config = Frontend::getConfig('system');
    $query = [
      $config['controllerArgument'] => $controller,
      $config['actionArgument'] => $action
    ];
    if( isset($arguments) && !empty($arguments) ){
      $query = ArrayUtility::combine($query,$arguments);
    }
    return implode('',[
      $_SERVER['REQUEST_SCHEME'],
      '://',
      $_SERVER['HTTP_HOST'],
      $_SERVER['SCRIPT_NAME'],
      '?',
      (is_null($query) ? '' : http_build_query($query))
    ]);
  }
  
  /** 
   * Performs a GET-Request to a given controller action pair with additional arguments
   *
   * @param string $controller The target controller
   * @param string $action The target controller action
   * @param array $arguments The additional arguments
   * @param bool $raw Flag to determine if the result of the request should be undecoded
   * @return string|array
   */
  public static function GET(
    string $controller,
    string $action,
    array $arguments=null,
    bool $raw=false
  ): string|array {
    $url = self::getUrl($controller,$action,$arguments);
    
    $ch = curl_init();
    curl_setopt_array($ch,[
      CURLOPT_URL => $url,
      CURLOPT_HTTPHEADER => self::getHeader(),
      CURLOPT_RETURNTRANSFER => TRUE,
      //CURLOPT_CAINFO => self::CAINFO,
      CURLOPT_SSL_VERIFYPEER => self::SELFSIGNED ? 0 : 1
    ]);
    
    $result = curl_exec($ch);
    
    if( self::$debug ){
      $json = json_decode($result,true);
      echo DebugUtility::var_dump([
        'url'=>$url,
        'result' => $result,
        'json'=>$json,
        'encoded' => json_encode($json,true),
        'error' => curl_error($ch),
      ],__CLASS__,__METHOD__,__LINE__);
    }
    
    curl_close($ch);
    
    return $raw ? $result : json_decode($result,true);
  }
  
  /** 
   * Performs a POST-Request to a given controller action pair with additional arguments
   *
   * @param string $controller The target controller
   * @param string $action The target controller action
   * @param array $arguments The payload for the request
   * @param bool $raw Flag to determine if the result of the request should be undecoded
   * @return string|array
   */
  public static function POST(
    string $controller,
    string $action,
    array $arguments=null,
    bool $raw=false
  ){
    $url = self::getUrl($controller,$action);
    
    $ch = curl_init();
    curl_setopt_array($ch,[
      CURLOPT_URL => $url,
      CURLOPT_POST => TRUE,
      CURLOPT_POSTFIELDS => json_encode($arguments),
      CURLOPT_HTTPHEADER => self::getHeader(),
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_SSL_VERIFYPEER => self::SELFSIGNED ? 0 : 1
    ]);
    
    $result = curl_exec($ch);
    
    if( self::$debug ){
      $json = json_decode($result,true);
      echo DebugUtility::var_dump([
        'url'=>$url,
        'payload' => json_encode($arguments),
        'result' => $result,
        'json'=>$json,
        'encoded' => json_encode($json,true),
        'error' => curl_error($ch),
      ],__CLASS__,__METHOD__,__LINE__);
    }
    
    curl_close($ch);
    
    return $raw ? $result : json_decode($result,true);
  }
}
