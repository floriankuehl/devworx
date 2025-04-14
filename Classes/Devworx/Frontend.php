<?php

namespace Devworx;

use \Devworx\ConfigManager;
use \Devworx\Utility\AuthUtility;
use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\CookieUtility;
use \Devworx\Utility\GeneralUtility;
use \Devworx\Utility\StringUtility;
use \Devworx\Utility\DebugUtility;
use \Devworx\Renderer\ConfigRenderer;
use \Api\Utility\ApiUtility;

class Frontend extends ConfigManager {
  
  const PATHGLUE = '/';
  const REALPATH = false;
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
    $action = '';
    
  public static function loadHeaders(){
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
  }
  
  public static function path(...$segments): string {
    $segments = array_map(fn($s)=>trim($s,self::PATHGLUE),$segments);
    $path = implode(self::PATHGLUE,[
      $GLOBALS['DEVWORX']['PATH']['ROOT'],
      ...$segments
    ]);
    return self::REALPATH ? realpath($path) : $path;
  }
  
  public static function pathDebug(...$segments){
    $segments = array_map(fn($s)=>trim($s,self::PATHGLUE),$segments);
    $path = implode(self::PATHGLUE,[
      $GLOBALS['DEVWORX']['PATH']['ROOT'],
      ...$segments
    ]);
    return [
      'list' => $segments,
      'path' => $path,
      'real' => realpath($path)
    ];
  }
  
  public static function publicPath(...$segments): string {
    $segments = array_map(fn($s)=>trim($s,self::PATHGLUE),$segments);
    $path = implode(self::PATHGLUE,[
      $GLOBALS['DEVWORX']['PATH']['PUBLIC'],
      ...$segments
    ]);
    return self::REALPATH ? realpath( $path ) : $path;
  }
  
  public static function viewPath(string $configKey,...$segments): string {
    $segments = array_map(fn($s)=>trim($s,self::PATHGLUE),$segments);
    $path = implode(self::PATHGLUE,[
      $GLOBALS['DEVWORX']['PATH']['PUBLIC'],
      self::getConfig('view',$configKey),
      ...$segments
    ]);
    return self::REALPATH ? realpath( $path ) : $path;
  }
  
  public static function scriptsPath(...$segments): string {
    return self::viewPath('scriptsPath',...$segments);
  }
  
  public static function stylesPath(...$segments): string {
    return self::viewPath('stylesPath',...$segments);
  }
  
  public static function isApiContext(): bool{
    return self::$context == self::CONTEXTS[1];
  }
  
  public static function loadConfigurationFile(): bool {
    $name = ucfirst(self::$context);
    $fileName = self::path('Configuration',"{$name}.json");
    return self::loadConfig($fileName);
  }
  
  public static function loadLayout(){
    $name = ucfirst(self::$config['layout']);
    $path = self::path( self::$config['layoutRootPath'], "{$name}.php" );
    if( is_file( $path ) )
      return file_get_contents( $path );
    throw new \Exception("Missing layout file: {$path}");
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
    self::loadHeaders();
    
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
      self::$config['context']['controller'] === self::$config['system']['defaultController'] && 
      self::$config['context']['action'] === self::$config['system']['defaultAction']
    );
  }
  
  public static function isPublicControllerAction(string $caPair=''): bool {
    if( empty($caPair) )
      $caPair = self::$config['context']['controller'] . '::' . self::$config['context']['action'];
    return in_array($caPair,self::$config['system']['publicControllerActions']);
  }
  
  public static function processControllerAction(): ?IController {
    $instance = self::loadController();
    if( is_null( $instance ) ) return $instance;
    self::$config['body']['content'] = $instance->processAction( self::$config['context']['action'] );
    return $instance;
  }
  
  public static function process(){
    if( self::initialize() ){
      
      $userOnline = self::isActiveLogin();
      $publicAction = self::isPublicControllerAction() || ( self::isApiContext() && $userOnline );
      
      if( !( self::isDefaultAction() || $publicAction || $userOnline ) ){
        self::redirectDefault();
      }
      
      $ctrl = self::processControllerAction();
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