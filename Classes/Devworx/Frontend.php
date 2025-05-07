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

/**
 * The main frontend class, that handles the basics of every requested page.
 * It also handles the current configuration by context
 */
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
  
  /** @var string The current context */
  public static $context = '';
  /** @var string The current encoding */
  public static $encoding = '';
  /** @var array The current header */
  public static $header = null;
  /** @var string The current layout */
  public static $layout = '';
  /** @var IController The current controller */
  public static $controller = null;
  /** @var string The current action */
  public static $action = '';
  
  /**
   * Loads the standard headers
   *
   * @return void
   */
  public static function loadHeaders(): void {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
  }
  
  /**
   * Gets a root folder or file path based on given segments
   *
   * @param array $segments The path segments
   * @return string
   */
  public static function path(...$segments): string {
    $segments = array_map(fn($s)=>trim($s,self::PATHGLUE),$segments);
    $path = implode(self::PATHGLUE,[
      $GLOBALS['DEVWORX']['PATH']['ROOT'],
      ...$segments
    ]);
    return self::REALPATH ? realpath($path) : $path;
  }
  
  /**
   * Gives debug information about a folder or file path based on given segments
   *
   * @param array $segments The path segments
   * @return array
   */
  public static function pathDebug(...$segments): array {
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
  
  /**
   * Gets a public folder or file path based on given segments
   *
   * @param array $segments The path segments
   * @return string
   */
  public static function publicPath(...$segments): string {
    $segments = array_map(fn($s)=>trim($s,self::PATHGLUE),$segments);
    $path = implode(self::PATHGLUE,[
      $GLOBALS['DEVWORX']['PATH']['PUBLIC'],
      ...$segments
    ]);
    return self::REALPATH ? realpath( $path ) : $path;
  }
  
  /**
   * Gets the public path based on the view configuration
   *
   * @param string $configKey The key of the view config to use
   * @param array $segments The path segments
   * @return string
   */
  public static function viewPath(string $configKey,...$segments): string {
    $segments = array_map(fn($s)=>trim($s,self::PATHGLUE),$segments);
    $path = implode(self::PATHGLUE,[
      $GLOBALS['DEVWORX']['PATH']['PUBLIC'],
      self::getConfig('view',$configKey),
      ...$segments
    ]);
    return self::REALPATH ? realpath( $path ) : $path;
  }
  
  /**
   * Gets the public scripts path
   *
   * @param array $segments The path segments
   * @return string
   */
  public static function scriptsPath(...$segments): string {
    return self::viewPath('scriptsPath',...$segments);
  }
  
  /**
   * Gets the public styles path
   *
   * @param array $segments The path segments
   * @return string
   */
  public static function stylesPath(...$segments): string {
    return self::viewPath('stylesPath',...$segments);
  }
  
  /**
   * Checks if the program runs in API context
   *
   * @return bool
   */
  public static function isApiContext(): bool{
    return self::$context == self::CONTEXTS[1];
  }
  
  /**
   * Loads the context based configuration file
   *
   * @return bool
   */
  public static function loadConfigurationFile(): bool {
    $name = ucfirst(self::$context);
    $fileName = self::path('Configuration',"{$name}.json");
    return self::loadConfig($fileName);
  }
  
  /**
   * Returns the content of the current layout file
   *
   * @return string
   */
  public static function loadLayout(): string {
    $name = ucfirst(self::$config['layout']);
    $path = self::path( self::$config['layoutRootPath'], "{$name}.php" );
    if( is_file( $path ) )
      return file_get_contents( $path );
    throw new \Exception("Missing layout file: {$path}");
	return '';
  }
  
  /**
   * Loads and returns the current controller instance
   *
   * @param string $namespace Allows a variable namespace for the FQCN
   * @return object|null
   */
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
  
  /**
   * Checks if a user is logged in
   *
   * @return bool
   */
  public static function isActiveLogin(): bool {
    return is_array( self::$config['user'] ) && !empty(self::$config['user']);
  }
  
  /**
   * Returns a URL for a controller action with optional GET-arguments
   *
   * @param string $controller The controller name
   * @param string $action The action name
   * @param array|null $arguments The additional arguments
   * @return string
   */
  public static function getUrl(string $controller,string $action,array $arguments=null): string {
    $formData = ArrayUtility::combine(
      [
        self::$config['system']['controllerArgument'] => $controller,
        self::$config['system']['actionArgument'] => $action
      ],
      $arguments
    );
    return "?" . http_build_query($formData);
  }
  
  /**
   * Redirects to a controller action with optional GET-arguments
   *
   * @param string $controller The controller name
   * @param string $action The action name
   * @param array|null $arguments The additional arguments
   * @return void
   */
  public static function redirect( string $controller, string $action, array $arguments=null ): void {
    GeneralUtility::redirect( 
      self::getUrl( $controller, $action, $arguments ) 
    );
  }
  
  /**
   * Redirects to the default controller action
   *
   * @param string $controller The controller name
   * @param string $action The action name
   * @return void
   */
  public static function redirectDefault(): void {
    self::redirect(
      self::$config['system']['defaultController'],
      self::$config['system']['defaultAction']
    );
  }
  
  /**
   * Redirects back to the referrer
   *
   * @return void
   */
  public static function redirectReferrer(){
    GeneralUtility::redirect( $_SERVER['HTTP_REFERER'] );
  }
  
  /**
   * Returns the current controller id based on set keys in $_REQUEST.
   * Falls back to the defaultController if not set
   *
   * @return string
   */
  public static function getCurrentController(): string {
    return StringUtility::cleanup(
      ArrayUtility::key(
        $_REQUEST, 
        self::$config['system']['controllerArgument'], 
        self::$config['system']['defaultController']
      )
    );
  }
  
  /**
   * Returns the current action name based on set keys in $_REQUEST.
   * Falls back to the defaultAction if not set
   *
   * @return string
   */
  public static function getCurrentAction(): string {
    return StringUtility::cleanup(
      ArrayUtility::key(
        $_REQUEST, 
        self::$config['system']['actionArgument'], 
        self::$config['system']['defaultAction']
      )
    );
  }
  
  /**
   * Returns the data of the current logged in user
   *
   * @return array|null
   */
  public static function getCurrentUser(): ?array {
    return self::$config['user'];
  }
  
  /**
   * Initializes the frontend
   *
   * @return bool
   */
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
	  $renderer = new ConfigRenderer();
      self::$config = $renderer->render( self::$config );
      return true;
    }
    return false;
  }
  
  /**
   * Checks if the current controller and action matches the default
   *
   * @return bool
   */
  public static function isDefaultAction(): bool {
    return (
      self::$config['context']['controller'] === self::$config['system']['defaultController'] && 
      self::$config['context']['action'] === self::$config['system']['defaultAction']
    );
  }
  
  /**
   * Checks if the current controller and action are public by configuration
   *
   * @return bool
   */
  public static function isPublicControllerAction(string $caPair=''): bool {
    if( empty($caPair) )
      $caPair = self::$config['context']['controller'] . '::' . self::$config['context']['action'];
    return in_array($caPair,self::$config['system']['publicControllerActions']);
  }
  
  /**
   * Processes the current controller action to the body content
   *
   * @return AbstractController|null
   */
  public static function processControllerAction(): ?AbstractController {
    $instance = self::loadController();
    if( is_null( $instance ) ) return $instance;
    self::$config['body']['content'] = $instance->processAction( self::$config['context']['action'] );
    return $instance;
  }
  
  /**
   * Processes the current frontend request and renders the view if needed
   *
   * @return string|null
   */
  public static function process(): ?string {
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
