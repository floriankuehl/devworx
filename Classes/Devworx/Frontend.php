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
use \Devworx\Utility\SessionUtility;
use \Api\Utility\ApiUtility;

/**
 * The main frontend class, that handles the basics of every requested page.
 * It also handles the current configuration by context
 */
class Frontend extends ConfigManager {
  
  const PATHGLUE = '/';
  const DEFAULT_CONTEXT = 'frontend';
  const CONTEXT_KEY = 'X-Devworx-Context';
  const SYSTEM_USER = [
    'uid' => 0,
    'firstName' => 'Devworx',
    'lastName' => 'System'
  ];
  
  /** @var string $context The current context */
  public static $context = '';
  /** @var string $encoding The current encoding */
  public static $encoding = '';
  /** @var array $header The current header */
  public static $header = null;
  /** @var string $layout The current layout */
  public static $layout = '';
  /** @var IController $controller The current controller */
  public static $controller = null;
  /** @var string $action The current action */
  public static $action = '';
  
  /**
   * Sets a header key-value-pair
   *
   * @return void
   */
  public static function setHeader(string $key, string $value): void {
	  header("{$key}: {$value}");
  }
  
  /**
   * Sets a header depending on a configuration value
   *
   * @return void
   */
  public static function setHeaderConfig(string $key, ...$path): void {
	  $value = self::getConfig(...$path);
	  if( is_null($value) )
		  throw new \Exception('missing config path: ' . implode('/',$path) . ' in context ' . self::$context);
	  self::setHeader($key, $value);
  }
  
  /**
   * Loads the standard headers from the configuration
   *
   * @return void
   */
  public static function loadHeaders(): void {
	self::setHeaderConfig('Content-Type', 'head','metaHttpEquiv','Content-Type');
	self::setHeaderConfig('Content-Script-Type', 'head','metaHttpEquiv','Content-Script-Type');
	self::setHeaderConfig('Content-Style-Type', 'head','metaHttpEquiv','Content-Style-Type');
	self::setHeaderConfig('Cache-Control', 'head','metaHttpEquiv','Cache-Control');
	self::setHeaderConfig('Pragma', 'head','metaHttpEquiv','Pragma');
	self::setHeaderConfig('Expires', 'head','metaHttpEquiv','Expires');
	self::setHeader( self::CONTEXT_KEY, self::$context );
  }
  
  /**
   * Builds a path based on a specific devworx path key
   *
   * @param string $rootKey The key in the global devworx path configuration
   * @param array $segments The path segments
   * @return string
   */
  public static function buildPath(string $rootKey,...$segments){
	$segments = array_map(fn($s)=>trim($s,self::PATHGLUE),$segments);
    return implode(self::PATHGLUE,[
      $GLOBALS['DEVWORX']['PATH'][$rootKey],
      ...$segments
    ]);
  }
  
  /**
   * Gets a root folder or file path based on given segments
   *
   * @param array $segments The path segments
   * @return string
   */
  public static function path(...$segments): string {
    return self::buildPath('ROOT',...$segments);
  }
  
  /**
   * Gets a root folder or file path based on given segments
   *
   * @param array $segments The path segments
   * @return string
   */
  public static function realPath(...$segments): string {
    return realpath( self::path(...$segments) );
  }
  
  /**
   * Gives debug information about a folder or file path based on given segments
   *
   * @param array $segments The path segments
   * @return array
   */
  public static function pathDebug(...$segments): array {
    $path = self::path(...$segments);
    return [
      'list' => $segments,
      'path' => $path,
      'real' => realpath($path)
    ];
  }
  
  /**
   * Gets the relative public folder or file path based on given segments
   *
   * @param array $segments The path segments
   * @return string
   */
  public static function publicPath(...$segments): string {
    return self::buildPath('PUBLIC',...$segments);
  }
  
  /**
   * Gets the real path of a public folder or file path based on given segments
   *
   * @param array $segments The path segments
   * @return string
   */
  public static function realPublicPath(...$segments): string {
    return realpath( self::publicPath(...$segments) );
  }
  
  /**
   * Creates a relative path between two paths
   *
   * @param string $from the source path
   * @param string $to the target path
   * @return string
   */
  public static function createRelativePath(string $from, string $to): string {
	$from = str_replace('\\', '/', realpath($from));
	$to = str_replace('\\', '/', realpath($to));

	$from = explode('/', rtrim($from, '/'));
	$to = explode('/', rtrim($to, '/'));

	while (count($from) && count($to) && ($from[0] === $to[0])) {
		array_shift($from);
		array_shift($to);
	}

	return str_repeat('../', count($from)) . implode('/', $to);
  }
  
  /**
   * Creates a relative path from the document root
   *
   * @param string $path the target path
   * @return string
   */
  public static function relativePath(string $path): string {
	return self::createRelativePath($_SERVER['DOCUMENT_ROOT'],$path);
  }
  
  /**
   * Gets the relative public path based on the view configuration
   *
   * @param string $configKey The key of the view config to use
   * @param array $segments The path segments
   * @return string
   */
  public static function viewPath(string $configKey,...$segments): string {
	return self::publicPath(
		self::getConfig('view',$configKey),
		...$segments
	);
  }
  
  /**
   * Gets the real public path based on the view configuration
   *
   * @param string $configKey The key of the view config to use
   * @param array $segments The path segments
   * @return string
   */
  public static function realViewPath(string $configKey,...$segments): string {
    return self::realPublicPath(
		self::getConfig('view',$configKey),
		...$segments
	);
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
   * Checks if the program context is known
   *
   * @param string $context The context key to check
   * @return bool
   */
  public static function isKnownContext(string $context): bool {
	  return ArrayUtility::has($GLOBALS['DEVWORX']['CONTEXT'],$context);
  }
  
  /**
   * Retrieves the context label
   *
   * @param string $context The context key to check
   * @return string
   */
  public static function getContextName(string $context): string {
	  return ArrayUtility::key($GLOBALS['DEVWORX']['CONTEXT'],$context);
  }
  
  /**
   * Checks if the program runs in Frontend context
   *
   * @return bool
   */
  public static function isFrontendContext(): bool{
    return self::$context === 'frontend';
  }
  
  /**
   * Checks if the program runs in API context
   *
   * @return bool
   */
  public static function isApiContext(): bool{
    return self::$context === 'api';
  }
  
  /**
   * Checks if the program runs in Frontend context
   *
   * @return bool
   */
  public static function isDocumentationContext(): bool{
    return self::$context === 'documentation';
  }
  
  /**
   * Loads the context based configuration file
   *
   * @param string $context
   * @return bool
   */
  public static function loadConfigurationFile(string $context): bool {
    $name = ucfirst($context);
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
   * @param string|null $anchor The optional anchor of the url
   * @return string
   */
  public static function getUrl(string $controller,string $action,array $arguments=null,string $anchor=null): string {
    $formData = ArrayUtility::combine(
      [
        self::$config['system']['controllerArgument'] => $controller,
        self::$config['system']['actionArgument'] => $action
      ],
      $arguments
    );
    return "?" . http_build_query($formData) . ( is_null($anchor) ? '' : "#{$anchor}" );
  }
  
  /**
   * Redirects to a controller action with optional GET-arguments
   *
   * @param string $controller The controller name
   * @param string $action The action name
   * @param array|null $arguments The additional arguments
   * @param string|null $anchor The optional anchor of the url
   * @return void
   */
  public static function redirect( string $controller, string $action, array $arguments=null, string $anchor=null ): void {
    GeneralUtility::redirect( 
      self::getUrl( $controller, $action, $arguments, $anchor ) 
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
   * Returns the uid of the current user
   *
   * @return string|null
   */
  public static function getCurrentUserId(): ?string {
    return is_array(self::$config['user']) ? self::$config['user']['uid'] : null;
  }
  
  /**
   * Retrieves the current context
   *
   * @return string
   */
  public static function getContext(): string {
	return ArrayUtility::key(
		self::$header,
		self::CONTEXT_KEY,
		ArrayUtility::key(
			$_SERVER,
			'REDIRECT_CONTEXT',
			self::DEFAULT_CONTEXT
		)
	);
  }
  
  
  /**
   * Handles global controller::action combination
   *
   * @param string $controller The controller
   * @param string $action The action
   * @return string
   */
  public static function controllerActionPair(string $controller,string $action): string {
	  //return strtolower( "{$controller}::{$action}" );
	  return ucfirst($controller) . '::' . ucfirst($action);
  }
  
  /**
   * Gets the systemwide default controller action
   *
   * @return string
   */
  public static function getDefaultControllerAction():string {
	return self::controllerActionPair(
		self::$config['system']['defaultController'],
		self::$config['system']['defaultAction']
	);
  }
  
  /**
   * Gets the current context controller action
   *
   * @return string
   */
  public static function getContextControllerAction():string {
	return self::controllerActionPair(
		self::$config['context']['controller'],
		self::$config['context']['action'] 
	);
  }
  
  /**
   * Loads a specific context
   *
   * @param string $context The context to load
   * @return bool
   */
  public static function loadContext(string $context): bool {
	  if( 
		self::isKnownContext($context) && 
		self::loadConfigurationFile($context) 
	  ){
		self::$context = $context;
		self::loadHeaders();
		self::$header = getallheaders();
		
		self::$config['context'] = [
			'controller' => self::getCurrentController(),
			'action' => self::getCurrentAction()
		];
		self::$config['user'] = AuthUtility::getCurrentUser();
		
		if( self::isApiContext() ){
			self::$encoding = 'json';
			ApiUtility::initialize();
			return true;
		}
		  
		$renderer = new ConfigRenderer();
		self::$config = $renderer->render( self::$config );
		  
		if( self::isDocumentationContext() )
			return true;
		  
		SessionUtility::start();
		  
		return true;
	  }
	  
	  throw new \Exception("unable to load configuration file");
	  return false;
  }
  
  /**
   * Initializes the frontend
   *
   * @return bool
   */
  public static function initialize(): bool {
	self::$header = getallheaders();
	return self::loadContext( self::getContext() );
  }
  
  
  
  /**
   * Checks if the current controller and action matches the default
   *
   * @return bool
   */
  public static function isDefaultAction(): bool {
	return self::getDefaultControllerAction() === self::getContextControllerAction();
  }
  
  /**
   * Checks if the current controller and action are public by configuration
   *
   * @param string $ca optional controller action to check, if empty, the current context controller action is checked
   * @return bool
   */
  public static function isPublicControllerAction(string $ca=null): bool {
    $ca = $ca ?? self::getContextControllerAction();
    return in_array($ca,self::$config['system']['publicControllerActions']);
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
		echo self::$context;
        throw new \Exception('Unknown Controller ' . self::$config['context']['controller']);
        return '';
      }
      
      if( $userOnline ){
        if( !self::isApiContext() )
          CookieUtility::refresh();
      }
      
      if( $ctrl->getBlockRendering() )
        return '';
      
	  if( $ctrl->getBlockLayout() )
		  return self::$config['body']['content'];
	  
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
