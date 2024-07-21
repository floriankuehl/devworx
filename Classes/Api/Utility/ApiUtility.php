<?php

namespace Api\Utility;

use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\DebugUtility;

use \Devworx\Frontend;

class ApiUtility {
  
  public static $debug = !true;
  
  const 
    ACCEPT_KEY = 'Accept',
    CONTENT_TYPE_KEY = 'Content-Type',
    CONTENT_TYPE = 'application/json',
    CHARSET = 'utf-8',
    HEADER_KEY = 'X-Devworx-Api',
    CONTEXT_KEY = 'X-Devworx-Context',
    CONTEXT = 'api';
    
  public static function hasKey(): bool {
    return Frontend::hasHeader(self::HEADER_KEY);
  }
  
  public static function getKey(): ?string {
    return ArrayUtility::key(Frontend::$header,self::HEADER_KEY);
  }
  
  public static function getHeaderString(string $key,string $value): string {
    return "{$key}: {$value}";
  }
  
  public static function setHeader(string $key,string $value): void {
    header(self::getHeaderString($key,$value));
  }
  
  public static function setHeaders(array $headers): void {
    foreach($headers as $key=>$value)
      self::setHeader($key,$value);
  }
  
  public static function initialize(): void {
    self::setHeaders([
      self::ACCEPT_KEY => self::CONTENT_TYPE,
      self::CONTENT_TYPE_KEY => self::CONTENT_TYPE . ';charset=' . self::CHARSET,
      'Keep-Alive' => 'timeout=5, max=100',
    ]);
  }
  
  public static function getHeader(): array {
    return [
      self::getHeaderString(self::CONTENT_TYPE_KEY,self::CONTENT_TYPE . ';charset=' . self::CHARSET),
      self::getHeaderString(self::CONTEXT_KEY,self::CONTEXT),
      self::getHeaderString(self::HEADER_KEY,Frontend::getConfig('user','login')),
    ];
  }
  
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
  
  public static function GET(
    string $controller,
    string $action,
    array $arguments=null,
    bool $raw=false
  ){
    $url = self::getUrl($controller,$action,$arguments);
    
    $ch = curl_init();
    curl_setopt_array($ch,[
      CURLOPT_URL => $url,
      CURLOPT_HTTPHEADER => self::getHeader(),
      CURLOPT_RETURNTRANSFER => TRUE
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
      CURLOPT_RETURNTRANSFER => TRUE
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