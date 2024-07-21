<?php

namespace Devworx;

use \Devworx\Utility\AuthUtility;
use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\CookieUtility;
use \Devworx\Utility\GeneralUtility;
use \Devworx\Utility\StringUtility;
use \Devworx\Utility\DebugUtility;
use \Devworx\Renderer\ConfigRenderer;
use \Api\Utility\ApiUtility;

class Frontend {
  
  const CONTEXTS = [ 'frontend', 'api' ];
  const CONTEXT_KEY = 'X-Devworx-Context';
  const SYSTEM_USER = [
    'uid' => 0,
    'firstName' => 'Devworx',
    'lastName' => 'System'
  ];
  
  public static
    $context = '',
    $encoding = '',
    $header = null,
    $layout = '',
    $controller = null,
    $action = '',
    $config = null;
  
  
  public static function isApiContext(): bool{
    return self::$context == self::CONTEXTS[1];
  }
  
  public static function loadConfigurationFile(): bool {
    $name = ucfirst(self::$context);
    $fileName = "Configuration/{$name}.json";
    if( is_file($fileName) ){
      self::$config = json_decode( file_get_contents( $fileName ), true );
      return is_array(self::$config);
    }
    return false;
  }
  
  public static function loadLayout(){
    $path = self::$config['layoutRootPath'];
    $name = ucfirst(self::$config['layout']);
    return file_get_contents( "{$path}/{$name}.php" );
  }
  
  public static function getConfig(...$keys){
    if( empty($keys) )
      return self::$config;
    
    $current = self::$config;
    foreach( $keys as $i=>$key ){
      if( is_array($current) && array_key_exists($key,$current) )
        $current = $current[$key];
      else {
        $current = null;
        break;
      }
    }

    return $current;
  }
  
  public static function loadController(string $namespace=''){
    $controller = self::$config['context']['controller'];
    $namespace = empty($namespace) ? self::$config['system']['defaultNamespace'] : $namespace;
    $controller = ucfirst($controller);
    $className = "{$namespace}\\Controller\\{$controller}Controller";
    if( class_exists($className) ){
      $instance = new $className();
      $instance->initialize();
      return $instance;
    }
    return null;
  }
  
  public static function isActiveLogin(): bool {
    return is_array( self::$config['user'] ) && !empty(self::$config['user']);
  }
  
  public static function getUrl(string $controller,string $action,array $arguments=null){
    $formData = ArrayUtility::combine(
      [
        self::$config['system']['controllerArgument'] => $controller,
        self::$config['system']['actionArgument'] => $action
      ],
      $arguments
    );
    return "?" . http_build_query($formData);
  }
  
  public static function redirect( string $controller, string $action, array $arguments=null ){
    GeneralUtility::redirect( 
      self::getUrl( $controller, $action, $arguments ) 
    );
  }
  
  public static function redirectDefault(){
    self::redirect(
      self::$config['system']['defaultController'],
      self::$config['system']['defaultAction']
    );
  }
  
  public static function redirectReferrer(){
    GeneralUtility::redirect( $_SERVER['HTTP_REFERER'] );
  }
  
  public static function getCurrentController(): string {
    return StringUtility::cleanup(
      ArrayUtility::key(
        $_REQUEST, 
        self::$config['system']['controllerArgument'], 
        self::$config['system']['defaultController']
      )
    );
  }
  
  public static function getCurrentAction(): string {
    return StringUtility::cleanup(
      ArrayUtility::key(
        $_REQUEST, 
        self::$config['system']['actionArgument'], 
        self::$config['system']['defaultAction']
      )
    );
  }
  
  public static function getCurrentUser(): ?array {
    return self::$config['user'];
  }
  
  public static function initialize(): bool {
    self::$header = getallheaders();
    self::$context = ArrayUtility::key(self::$header,self::CONTEXT_KEY,self::CONTEXTS[0]);
    
    if( self::loadConfigurationFile() ){
      self::$config['context'] = [
        'controller' => self::getCurrentController(),
        'action' => self::getCurrentAction(),
      ];
      self::$config['user'] = AuthUtility::getCurrentUser();
            
      if( self::isApiContext() ){
        self::$encoding = 'json';
        ApiUtility::initialize();
        return true;
      }
      
      session_start();
      self::$config = ConfigRenderer::render( self::$config );
      return true;
    }
    return false;
  }
  
  public static function isDefaultAction(): bool {
    return (
      self::$config['context']['controller'] == self::$config['system']['defaultController'] && 
      self::$config['context']['action'] == self::$config['system']['defaultAction']
    );
  }
  
  public static function controllerAction(): ?IController {
    $instance = self::loadController();
    if( is_null( $instance ) )
      return $instance;
    self::$config['body']['content'] = $instance->processAction( self::$config['context']['action'] );
    return $instance;
  }
  
  public static function process(){
    if( self::initialize() ){
      $userOnline = self::isActiveLogin();
      $defaultAction = self::isDefaultAction();
      
      if( !( $defaultAction || $userOnline ) ){
        self::redirectDefault();
      }
      
      $ctrl = self::controllerAction();
      if( is_null($ctrl) ){
        throw new \Exception('Unknown Controller ' . self::$config['context']['controller']);
        return '';
      }
      
      if( $userOnline ){
        if( !self::isApiContext() )
          CookieUtility::refresh();
      }
      
      if( $ctrl->getBlockRendering() )
        return '';
      
      return View::Layout( 
        self::$config['view']['layout'], 
        self::$config, 
        '', 
        self::$encoding 
      )->render();
    }
    return null;
  }
  
}


?>