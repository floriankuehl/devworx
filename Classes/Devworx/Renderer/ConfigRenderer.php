<?php

namespace Devworx\Renderer;

use Devworx\Html;

class ConfigRenderer extends AbstractRenderer {
  
  /**
   * Renders a meta-tag with name and content
   *
   * @param string $key The name attribute value
   * @param string $value The content attribute value
   * @return string
   */
  public static function meta(string $key,string $value): string {
    return Html::element('meta',['name'=>$key,'content'=>$value],null);
  }
  
  /**
   * Renders a meta-tag with http-equiv and content
   *
   * @param string $key The http-equiv attribute value
   * @param string $value The content attribute value
   * @return string
   */
  public static function metaHttpEquiv(string $key,string $value){
    return Html::element('meta',['http-equiv'=>$key,'content'=>$value],null);
  }
  
  /**
   * Renders a script-tag with id, src and type
   *
   * @param string $key The id attribute value
   * @param string $value The src attribute value
   * @param bool $module If true, the script will be defined with type 'module'
   * @return string
   */
  public static function script(string $key,string $value,bool $module=false){
    return Html::element('script',['id'=>$key,'src'=>$value,'type'=>($module ? 'module' : 'text/javascript')],'');
  }
  
  /**
   * Renders a script-tag with id and body
   *
   * @param string $key The id attribute value
   * @param string $value The body of the element
   * @return string
   */
  public static function inlineScript(string $key,string $value){
    return Html::element('script',['id'=>$key],$value);
  }
  
  /**
   * Renders a link-tag with id and href for stylesheets
   *
   * @param string $key The id attribute value
   * @param string $value The href attribute value
   * @return string
   */
  public static function stylesheet(string $key,string $value){
    return Html::element('link',['rel'=>'stylesheet','id'=>$key,'href'=>$value]);
  }
  
  /**
   * Renders a style-tag with id to import a CSS file
   *
   * @param string $key The id attribute value
   * @param string $value The path to the CSS file
   * @return string
   */
  public static function inlineStylesheet(string $key,string $value){
    return Html::element('style',['id'=>$key],"@import url('{$value}');");
  }
  
  /**
   * Collapses data with a given method
   *
   * @param mixed $data The data to collapse
   * @param string $method The class method to perform the collapse. if empty, implode will be used
   * @param mixed $args Arguments for the manual collapse method
   * @return string
   */
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
  
  /**
   * Collapses a configuration manually with a given method
   *
   * @param array $config The configuration array
   * @param string $key The key of the configuration data to collapse
   * @param string $method The class method to perform the collapse. if empty, implode will be used
   * @param mixed $args Arguments for the manual collapse method
   * @return string
   */
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
  
  public function supports(mixed $template): bool {
	  return is_array($template);
  }
  
  /**
   * Renders a configuration with automatic collapsing
   *
   * @param mixed $source The configuration data
   * @param array $variables The variables to provide while rendering
   * @param string $encoding The standard encoding for the renderer
   * @return mixed
   */
  public function render(mixed $source,array $variables=null,string $encoding=''): mixed {
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
