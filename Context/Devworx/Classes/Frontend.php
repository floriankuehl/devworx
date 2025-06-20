<?php

namespace Devworx;

use \Devworx\Configuration;
use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\CookieUtility;
use \Devworx\Utility\StringUtility;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\DebugUtility;

use \Devworx\Controller\AbstractController;

/**
 * The main frontend class, that handles the basics of every requested page.
 * It also handles the current configuration by context
 */
class Frontend {

	/** @const array the system user information (uid 0) */
	const SYSTEM_USER = [
		'uid' => 0,
		'firstName' => 'Devworx',
		'lastName' => 'System'
	];
	
	/** @var string $encoding The current encoding */
	public static $encoding = '';
	
	/**
	 * Checks if a user is logged in
	 *
	 * @return bool
	 */
	public static function isActiveLogin(): bool {
		return Configuration::isFilledArray('context','user');
	}
	
	/**
	 * Returns the data of the current logged in user
	 *
	 * @return array|null
	 */
	public static function getCurrentUser(): ?array {
		return Configuration::get('context','user');
	}

	/**
	 * Returns the uid of the current user
	 *
	 * @return string|null
	 */
	public static function getCurrentUserId(): ?string {
		return Configuration::get('context','user','uid');
	}
	
	/**
	 * Returns the hash of the current user
	 *
	 * @return string|null
	 */
	public static function getCurrentUserHash(): ?string {
		return Configuration::get('context','user','login');
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
				Configuration::get('system','controllerArgument'), 
				Configuration::get('system','defaultController')
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
				Configuration::get('system','actionArgument'), 
				Configuration::get('system','defaultAction')
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
			Configuration::get('system','defaultController'),
			Configuration::get('system','defaultAction')
		);
	}

	/**
	 * Gets the current context controller action
	 *
	 * @return string
	 */
	public static function getContextControllerAction():string {
		return self::controllerActionPair(
			Configuration::get('context','controller'),
			Configuration::get('context','action')
		);
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
		return in_array($ca,Configuration::get('system','publicControllerActions'));
	}

	/**
	 * Loads and returns the current controller instance
	 *
	 * @param string $context allows access to other contexts
	 * @return object|null
	 */
	public static function loadController(string $context=''){
		
		Performance::start(__METHOD__);
		
		$controller = ucfirst(Configuration::get('context','controller'));

		$context = empty($context) ? Context::get() : $context;
		$className = "{$context}\\Controller\\{$controller}Controller";

		if( class_exists($className) ){
			$instance = new $className();
			$instance->initialize();
			Performance::stop(__METHOD__);
			return $instance;
		} else {
			Performance::stop(__METHOD__);
			throw new \Exception("Controller $className not found!<br>");
		}
		Performance::stop(__METHOD__);
		return null;
	}
	
	/**
	 * Returns the content of the current layout file
	 *
	 * @return string
	 */
	public static function loadLayout(): string {
		$name = ucfirst( Configuration::get('layout') );
		$path = PathUtility::view( 'layoutRootPath', "{$name}.php" );
		if( file_exists( $path ) )
			return file_get_contents( $path );
		throw new \Exception("Missing layout file: {$path}");
		return '';
	}

	/**
	 * Processes the current controller action to the body content
	 *
	 * @return AbstractController|null
	 */
	public static function processControllerAction(): ?AbstractController {
		Performance::start(__METHOD__);
		$instance = self::loadController();
		if( is_null( $instance ) ){
			Performance::stop(__METHOD__);
			return $instance;
		}
		Configuration::set(
			$instance->processAction( Configuration::get('context','action') ),
			'body', 'content'
		);
		Performance::stop(__METHOD__);
		return $instance;
	}

	/**
	 * Processes the current frontend request and renders the view if needed
	 *
	 * @return string|null
	 */
	public static function process(): ?string {
		
		Performance::start(__METHOD__);
		if( Context::load() ){
			$userOnline = self::isActiveLogin();
			$publicAction = self::isPublicControllerAction();
			$isApiCall = Context::is('Api') && $userOnline;

			if( !( self::isDefaultAction() || $publicAction || $userOnline || $isApiCall ) ){
				Performance::stop(__METHOD__);
				Redirect::default();
			}

			if( $userOnline && !$isApiCall ){
				CookieUtility::refresh();
			}

			$ctrl = self::processControllerAction();
			if( is_null($ctrl) ){
				Performance::stop(__METHOD__);
				throw new \Exception("unknown controller {$context}\\Controller\\" . Configuration::get('context','controller'));
				return '';
			}

			if( $ctrl->getBlockRendering() ){
				Performance::stop(__METHOD__);
				return '';
			}
			if( $ctrl->getBlockLayout() ){
				Performance::stop(__METHOD__);
				return Configuration::$config['body']['content'];
			}

			Performance::start(__METHOD__.'::render');
			$result = View::Layout( 
				Configuration::get('view','layout'), 
				Configuration::get(), 
				'', 
				self::$encoding 
			)->render();
			Performance::stop(__METHOD__.'::render');
			
			Performance::stop(__METHOD__);
			return $result;
		}
		Performance::stop(__METHOD__);
		return null;
	}
	
	/**
	 * Initializes the Autoloader, Database and Caches, is called in devworx.php
	 *
	 * @return void
	 */
	public static function initialize(): void {
		
		require_once("../Context/Devworx/Classes/Autoloader.php");
		
		Autoloader::initialize();
		spl_autoload_register([Autoloader::class,'load'], true, true);
		set_exception_handler( [ Utility\DebugUtility::class, 'exception' ] );
		
		Performance::initialize(__METHOD__);
		
		/*
		Services::set('db', fn() => Database::class);
		Services::set('devworx.cfg', fn() => $GLOBALS['DEVWORX']['CFG']);
		Services::set('devworx.path', fn() => $GLOBALS['DEVWORX']['PATH']);
		Services::set('contexts', fn() => $GLOBALS['DEVWORX']['CONTEXT']);
		Services::set('autoloader', fn() => Autoloader::class);
		Services::set('cache', fn() => Caches::class);
		Services::set('debugger', fn() => \Devworx\Utility\DebugUtility::class);
		*/
		
		Performance::start(__METHOD__.'::Database');
		Database::initialize(...$GLOBALS['DEVWORX']['DB']);
		Performance::stop(__METHOD__.'::Database');
		
		Performance::start(__METHOD__.'::Caches');
		Caches::initialize();
		Performance::stop(__METHOD__.'::Caches');
		
		Performance::start(__METHOD__.'::ContextMap');
		Autoloader::loadCachedContextMap();
		Performance::stop(__METHOD__.'::ContextMap');
		
		Performance::stop(__METHOD__);
		// Optional: OPCache Precompile Tool aktivieren
		// Utility::OPCacheUtility::build();
		
	}
  
}


?>
