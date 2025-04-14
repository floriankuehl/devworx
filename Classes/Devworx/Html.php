<?php

namespace Devworx;

class Html {

  public static function element(string $tag,array $attributes=null,$html=null){
    $attr = [];
    if( is_array($attributes) ){
      foreach( $attributes as $k => $v ){
        $attr[]= $k . ( is_null($v) ? '' : "=\"{$v}\"" );
      }
    }
    $attributes = empty($attr) ? "" : " " . implode(" ",$attr);
    
    if( is_null( $html ) )
      $html = '/';
    elseif( is_array($html) )
      $html = ">" . implode(PHP_EOL,$html) . "</{$tag}";
    else
      $html = ">{$html}</{$tag}";
    return "<{$tag}{$attributes}{$html}>";
  }

  public function __call(string $tag,array $args=[]){
    return self::element($tag,...$args);
  }
  
  public static function __callStatic(string $tag,array $args=[]){
    return self::element($tag,...$args);
  }
  
  public static function devworx(string $type,array $attributes=null,$html=null){
    return self::element("devworx-{$type}",$attributes,is_null($html) ? '' : $html);
  }

  public static function detectInput(
    AbstractModel $model,
    string $field,
    array $attributes=[],
    array $options=null
  ){
    $class = new \ReflectionClass(get_class($model));
    $func = $class->getMethod('get' . ucfirst($field));
    $type = "" . $func->getReturnType();
    unset($func);
    
    $class = explode("\\",$class->getName());
    $prefix = lcfirst(array_pop($class));
    //$prefix = empty($prefix) ? lcfirst($class) : $prefix;
    
    $value = $model->{'get' . ucfirst($field)}();
    $tag = "input";
    $html = null;
    $attributes['id'] = "{$prefix}_{$field}";
    $attributes['name'] = "{$prefix}[{$field}]";
    
    if( is_array($options) ){
      $tag = "select";
      $html = [];
      foreach( $options as $k=>$v ){
        if( is_array($v) ){
          $html []= "<optgroup label=\"{$k}\">";
          foreach( $v as $kv=>$vv ){
            $html []= "<option".($kv == $value?" selected":"")." value=\"{$kv}\">{$vv}</option>";
          }
          $html []= "</optgroup>";
          continue;
        }
        $html []= "<option".($k == $value?" selected":"")." value=\"{$k}\">{$v}</option>";
      }
      return self::element($tag,$attributes,$html);
    }
       
    switch($type){
      case'string': {
        $tag = "input";
        if( !array_key_exists('type',$attributes) )
          $attributes['type'] = 'text';
        $attributes['value'] = $value;
      } break;
      case'int': {
        $tag = "input";
        if( !array_key_exists('type',$attributes) )
          $attributes['type'] = 'number';
        $attributes['value'] = $value;
      } break;
      case'?DateTime': {
        $tag = "input";
        if( !array_key_exists('type',$attributes) )
          $attributes['type'] = 'datetime-local';
        $attributes['value'] = is_null($value) ? '' : $value->format("Y-m-d\TH:i:s");
      } break;
      case'DateTime': {
        $tag = "input";
        if( !array_key_exists('type',$attributes) )
          $attributes['type'] = 'datetime-local';
        $attributes['value'] = is_null($value) ? '' : $value->format("Y-m-d\TH:i:s");
      } break;
    }
    /*
    echo \Devworx\Utility\DebugUtility::var_dump([
      'tag' => $tag,
      'attributes' => $attributes,
      'html' => $html,
      'type' => $type
    ]);
    */
    
    return self::element($tag,$attributes,$html);
  }
}
?>