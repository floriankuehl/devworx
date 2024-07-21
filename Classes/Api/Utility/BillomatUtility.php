<?php

namespace Api\Utility;

use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\DebugUtility;
use \Devworx\Frontend;

class BillomatUtility {
  
  public static $debug = !true;
  
  const 
    ACCEPT_KEY = 'Accept',
    CONTENT_TYPE_KEY = 'Content-Type',
    CONTENT_TYPE = 'application/json',
    CHARSET = 'utf-8',
    APIKEY_KEY = 'X-BillomatApiKey',
    APPID_KEY = 'X-AppId',
    APPSECRET_KEY = 'X-AppSecret';
    
  public static function getHeaderString(string $key,string $value): string {
    return "{$key}: {$value}";
  }
  
  public static function getCache(string $resource,string $id='',array $filter=null){
    $result = null;
    $fileName = "Cache/Billomat/{$resource}" . ( empty($id) ? '' : "/{$id}" ) . ".json";
    if( is_file($fileName) ){
      
      $cache = json_decode(file_get_contents($fileName),true);
      
      if( is_array($cache) ){
        $result = $cache;
        if( is_array($filter) ){
          
          $data = $cache[$resource];
          $key = array_keys($data);
          $key = $key[0];
          
          $data = $data[$key];
          $value = ArrayUtility::filter($data,$filter);
          $result = [
            $resource => [
              $key => $value
            ]
          ];
        }
      }
    }
    return $result;
  }
  
  public static function setCache(string $resource,string $id='',$value){
    $fileName = "Cache/Billomat";
    if( !is_dir($fileName) ) 
      mkdir($fileName);
    
    if( empty($id) ){
      $fileName = "{$fileName}/{$resource}.json";
    } else {
      if( !is_dir("{$fileName}/{$resource}") ) 
        mkdir("{$fileName}/{$resource}",0x777,true);
      $fileName = "{$fileName}/{$resource}/{$id}.json";
    }
    file_put_contents($fileName,$value);
  }
  
  public static function setHeader(string $key,string $value): void {
    header(self::getHeaderString($key,$value));
  }
  
  public static function setHeaders(array $headers): void {
    foreach($headers as $key=>$value)
      self::setHeader($key,$value);
  }
  
  public static function getHeader(string $apiKey,string $appID,string $appSecret): array {
    return [
      self::getHeaderString(self::ACCEPT_KEY,self::CONTENT_TYPE),
      self::getHeaderString(self::CONTENT_TYPE_KEY,self::CONTENT_TYPE . ';charset=' . self::CHARSET),
      self::getHeaderString(self::APIKEY_KEY,$apiKey),
      self::getHeaderString(self::APPID_KEY,$appID),
      self::getHeaderString(self::APPSECRET_KEY,$appSecret),
    ];
  }
  
  public static function getUrl(
    string $billomatId,
    string $resource,
    string $id='',
    string $method='',
    array $arguments=null
  ): string {
    return implode('',[
      'https://',
      "{$billomatId}.billomat.net/api",
      "/{$resource}" . 
      ( empty($id) ? $id : "/{$id}" ),
      ( empty($method) ? $method : "/{$method}" ),
      '?',
      (is_null($arguments) ? '' : http_build_query($arguments))
    ]);
  }
  
  public static function GETAll(string $resource){
        
    $activePage = 0;
    $perPage = 100;
    $singular = substr($resource,0,strlen($resource)-1);
    
    $result = self::getCache($resource);
    if( is_array($result) )
      return $result;
    
    $result = [];
    $page = self::GET($resource,'','',[
      'per_page' => $perPage,
      'page' => ++$activePage,
    ]);
    $data = $page[$resource][$singular];
    $result []= $data;
    
    while( count($data) == $perPage ){
      $page = self::GET($resource,'','',[
        'per_page' => $perPage,
        'page' => ++$activePage,
      ]);
      $data = $page[$resource][$singular];
      $result []= $data;
    }
    
    $result = [
      $resource=>[
        $singular=>array_merge(...$result)
      ]
    ];
    self::setCache($resource,'',json_encode($result));
    return $result;
  }
  
  public static function GET(
    string $resource,
    string $id='',
    string $method='',
    array $arguments=null,
    bool $raw=false
  ){
    $config = Frontend::getConfig("billomat");
    $url = self::getUrl($config['id'],$resource,$id,$method,$arguments);
    
    if( empty($method) ){
      $result = self::getCache($resource,$id,$arguments);
      if( is_array($result) )
        return $result;
    }
    
    $ch = curl_init();
    curl_setopt_array($ch,[
      CURLOPT_URL => $url,
      CURLOPT_HTTPHEADER => self::getHeader(
        $config["apiKey"],
        $config["appId"],
        $config["appSecret"]
      ),
      CURLOPT_RETURNTRANSFER => TRUE
    ]);
    
    $result = curl_exec($ch);
    
    if( empty($method) )
      self::setCache($resource,$id,$result);
    
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
    string $resource,
    string $id='',
    string $method='',
    array $body=null,
    bool $raw=false
  ){
    $config = Frontend::getConfig("billomat");
    $url = self::getUrl($config['id'],$resource,$id,$method);
    
    $ch = curl_init();
    curl_setopt_array($ch,[
      CURLOPT_URL => $url,
      CURLOPT_POST => TRUE,
      CURLOPT_POSTFIELDS => json_encode($body),
      CURLOPT_HTTPHEADER => self::getHeader(
        $config["apiKey"],
        $config["appId"],
        $config["appSecret"]
      ),
      CURLOPT_RETURNTRANSFER => TRUE
    ]);
    
    $result = curl_exec($ch);
    
    if( self::$debug ){
      $json = json_decode($result,true);
      echo DebugUtility::var_dump([
        'url'=>$url,
        'payload' => json_encode($body),
        'result' => $result,
        'json'=>$json,
        'encoded' => json_encode($json,true),
        'error' => curl_error($ch),
      ],__CLASS__,__METHOD__,__LINE__);
    }
    
    curl_close($ch);
    
    return $raw ? $result : json_decode($result,true);
  }
  
  public static function getClientProperty(array $client,string $propertyID){
    if( empty($client) || empty($propertyID) )
      return false;
    if( 
      array_key_exists('client-property-values',$client) &&
      is_array($client['client-property-values'])
    ){
      $result = $client['client-property-values']['client-property-value'];
      if( empty($result) ) 
        return false;
      if( array_key_exists('client_property_id',$result) )
        $result = [$result];
      
      $result = array_filter($result,function($row)use($propertyID){
        return $row['client_property_id'] == $propertyID;
      });
      if( !empty($result) ) return $result[0];
    }
    return false;
  }
  
  public static function customerByProperty(array &$customers,string $propertyID, string $propertyValue): ?array {
    $result = null;
    if( is_null($customers) || empty($customers) ){
      $customers = self::GET('clients');
      $customers = $customers['clients']['client'];
    }
    
    foreach( $customers as $i => $customer ){
      $property = self::getClientProperty($customer,$propertyID);
      if( 
        is_array($property) && 
        ( $property['value'] == $propertyValue ) 
      ){
        $result = $customer;
        break;
      }
    }
    return $result;
  }
}