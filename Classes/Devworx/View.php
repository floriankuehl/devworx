<?php

namespace Devworx;

/*
  EXTRACT FLAGS
    EXTR_OVERWRITE
    EXTR_SKIP
    EXTR_PREFIX_SAME
    EXTR_PREFIX_ALL
    EXTR_PREFIX_INVALID
    EXTR_IF_EXISTS
    EXTR_PREFIX_IF_EXISTS
*/

interface IView {
  
  function getId(): string;
  function setId(string $id): void;
  function getFile(): string;
  function setFile(string $fileName): void;
  function getProvideAll(): bool;
  function setProvideAll(bool $all): void;
  function getEncoding(): string;
  function setEncoding(string $encoding): void;
  function getVariables(): array;
  function setVariables(array $variables): void;
  function getRenderer(): string;
  function setRenderer(string $renderer): void;
  
  function hasVariable(string $key): bool;
  function getVariable(string $key);
  function setVariable(string $key,$value): void;
  
  function assign(string $key,$value): void;
  function assignMultiple(array $values): void;
  
  function render();
  static function renderStatic(
    string $fileName,
    array $variables,
    string $renderer,
    string $encoding
  );
}

class View implements IView {
  
  protected 
    $id = '',
    $fileName = '',
    $variables = [],
    $encoding = '',
    $renderer = '',
    $all = false;
  
  function __construct(
    string $id='',
    string $fileName='',
    array $variables=null,
    string $renderer='',
    string $encoding=''
  ){
    $this->id = $id;
    $this->fileName = $fileName;
    $this->variables = is_null($variables) ? [] : $variables;
    $this->renderer = empty($renderer) ? Frontend::getConfig('view','renderer') : $renderer;
    $this->encoding = $encoding;
  }
  
  function getId(): string {
    return $this->id;
  }
  
  function setId(string $id): void {
    $this->id = $id;
  }
  
  function getRenderer(): string {
    return $this->renderer;
  }
  
  function setRenderer(string $renderer): void {
    $this->renderer = $renderer;
  }
  
  function getFile(): string {
    return $this->fileName;
  }
  
  function setFile(string $fileName): void {
    $this->fileName = $fileName;
  }
  
  function getProvideAll(): bool {
    return $this->all;
  }
  
  function setProvideAll(bool $all=true): void {
    $this->all = $all;
  }
  
  function getEncoding(): string {
    return $this->encoding;
  }
  
  function setEncoding(string $encoding): void {
    $this->enconding = $encoding;
  }
  
  function getVariables(): array {
    return $this->variables;
  }
  
  function setVariables(array $variables): void {
    $this->variables = $variables;
  }
  
  function hasVariable(string $key): bool {
    return array_key_exists($key,$this->variables);
  }
  
  function getVariable(string $key){
    if( $this->hasVariable($key) )
      return $this->variables[$key];
    return null;
  }
  
  function setVariable(string $key,$value): void {
    $this->variables[$key] = $value;
  }
  
  function assign(string $key,$value): void {
    $this->setVariable($key,$value);
  }
  
  function assignMultiple(array $values): void {
    foreach( $values as $key => $value )
      $this->setVariable($key,$value);
  } 
  
  function render(){
    return self::renderStatic(
      $this->fileName,
      $this->variables,
      $this->renderer,
      $this->encoding
    );
  }
  
  function __toString(): string {
    return $this->render();
  }
  
  static function renderStatic(
    string $fileName,
    array $variables=null,
    string $renderer = '',
    string $encoding = ''
  ){
    $result = null;
    if( is_file($fileName) ){
      ob_start();
      if( is_array($variables) && !empty($variables) )
        extract(
          $variables,
          EXTR_OVERWRITE
        );
      $result = include($fileName);
      if( is_numeric($result) )
        $result = ob_get_clean();
      else
        ob_end_flush();
      
      return call_user_func([$renderer,'render'],$result,$variables,$encoding);
    }
    throw new \Exception("Missing file {$fileName}");
    return $result;
  }
  /*
    string $id='',
    string $fileName='',
    array $variables=[],
    string $renderer='',
    string $encoding=''
  */
  static function Layout(string $layout,array $arguments=null,string $renderer='',string $encoding=''): IView {
    $layout = ucfirst($layout);
    $path = Frontend::getConfig('view','layoutRootPath');
    $renderer = empty($renderer) ? Frontend::getConfig('view','renderer') : $renderer;
    return new View(
      $layout,
      "{$path}/{$layout}.php",
      $arguments,
      $renderer,
      $encoding
    );
  }
  
  static function Template(string $controller,string $action,array $arguments=null,string $renderer='',string $encoding=''): IView {
    $controller = ucfirst($controller);
    $action = ucfirst($action);
    $path = Frontend::getConfig('view','templateRootPath');
    $renderer = empty($renderer) ? Frontend::getConfig('view','renderer') : $renderer;
    return new View(
      "{$controller}-{$action}",
      "{$path}/{$controller}/{$action}.php",
      $arguments,
      $renderer,
      $encoding
    );
  }
  
  static function Partial(string $partial,array $arguments=null,string $renderer='',string $encoding=''): IView {
    $partial = ucfirst($partial);
    $path = Frontend::getConfig('view','partialRootPath');
    $renderer = empty($renderer) ? Frontend::getConfig('view','renderer') : $renderer;
    return new View(
      $partial,
      "{$path}/{$partial}.php",
      $arguments,
      $renderer,
      $encoding
    );
  }
    
}

?>