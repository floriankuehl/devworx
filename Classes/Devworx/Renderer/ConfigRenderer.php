<?php

namespace Devworx\Renderer;

use Devworx\Html;

class ConfigRenderer extends AbstractRenderer {
  
  public static function meta(string $key,string $value){
    return Html::element('meta',['name'=>$key,'content'=>$value],null);
  }
  
  public static function metaHttpEquiv(string $key,string $value){
    return Html::element('meta',['http-equiv'=>$key,'content'=>$value],null);
  }
  
  public static function script(string $key,string $value,bool $module=false){
    return Html::element('script',['id'=>$key,'src'=>$value,'type'=>($module ? 'module' : 'text/javascript')],'');
  }
  
  public static function inlineScript(string $key,string $value){
    return Html::element('script',['id'=>$key],$value);
  }
  
  public static function stylesheet(string $key,string $value){
    return Html::element('link',['rel'=>'stylesheet','id'=>$key,'href'=>$value]);
  }
  
  public static function inlineStylesheet(string $key,string $value){
    return Html::element('style',['id'=>$key],"@import url('{$value}');");
  }
  
  public static function collapse($data,string $method='',...$args): string {
    $result = '';
    if( is_null($data) || empty($data) ) 
      return $result;
    
    if( is_array($data) ){
      if( empty($method) ) 
        return implode(PHP_EOL,$data);
      
      $result = [];
      foreach( $data as $key => $value ){
        $result []= call_user_func([self::class,$method],$key,$value,...$args);
      }
      return implode(PHP_EOL,$result);
    }
    
    return $data;
  }
  
  public static function collapseManual(
    array &$config,
    string $key,
    string $method='',
    ...$args
  ){
    if( array_key_exists($key,$config) )
      $config[$key] = self::collapse(
        $config[$key],
        empty($method) ? $key : $method,
        ...$args
      );
  }
  
  public static function render($source,array $variables=null,string $encoding=''){
    if( is_array($source) ){
      foreach( $source as $key => $value ){
        if( is_array($value) ){
          switch($key){
            case'head':{
              self::collapseManual($value,'meta');
              self::collapseManual($value,'metaHttpEquiv');
              self::collapseManual($value,'styles','stylesheet');
              self::collapseManual($value,'scripts','script');
            }break;
            case'body':{
              self::collapseManual($value,'styles','inlineStylesheet');
              self::collapseManual($value,'scripts','script',true);
            }break;
          }
          $source[$key] = $value;
        }
      }
    }
    return $source;
  }
}

?>