<?php

namespace Devworx;

use \Devworx\Frontend;
use \Devworx\Configuration;
use \Devworx\Performance;

use \Devworx\Utility\AuthUtility;
use \Devworx\Utility\SessionUtility;
use \Devworx\Utility\ApiUtility;

class Context {
	
	/** @const array mapping between Frontend and Configuration */
	const CONFIGURABLE_HEADERS = [
		'Content-Type' => ['head','metaHttpEquiv','Content-Type'],
		'Content-Script-Type' => ['head','metaHttpEquiv','Content-Script-Type'],
		'Content-Style-Type' => ['head','metaHttpEquiv','Content-Style-Type'],
		'Cache-Control' => ['head','metaHttpEquiv','Cache-Control'],
		'Pragma' => ['head','metaHttpEquiv','Pragma'],
		'Expires' => ['head','metaHttpEquiv','Expires']
	];
	
	/**
	 * Gets the global framework variable
	 *
	 * @return string
	 */
	public static function framework(): string {
		return $GLOBALS['DEVWORX']['FRAMEWORK'];
	}
	
	/**
	 * Gets the global context variable
	 *
	 * @return string
	 */	
	public static function get(): string {
		return $GLOBALS['DEVWORX']['CONTEXT'];
	}
	
	/**
	 * Sets the global context variable
	 *
	 * @param string $context the new context
	 * @return void
	 */	
	public static function set(string $context): void {
		$GLOBALS['DEVWORX']['CONTEXT'] = $context;
	}
	
	/**
	 * Checks if the program runs in a specific context
	 *
	 * @param string $context the context to check against
	 * @return bool
	 */
	public static function is(string $context): bool{
		return self::get() === $context;
	}
	
	/**
	 * Checks if the program context is known
	 *
	 * @param string $context The context key to check
	 * @return bool
	 */
	public static function known(string $context): bool {
		return in_array($context,self::contexts());
	}
	
	/**
	 * Gets the global contexts list
	 *
	 * @return array
	 */	
	public static function contexts(): array {
		return $GLOBALS['DEVWORX']['CONTEXTS'];
	}
	
	/**
	 * Sets the global contexts list
	 *
	 * @param array $contexts the list to set
	 * @return void
	 */	
	public static function setContexts(array $contexts): void {
		$GLOBALS['DEVWORX']['CONTEXTS'] = $contexts;
	}
	
	/**
	 * Retrieves the current context with fallback to the framework name
	 *
	 * @return string
	 */
	public static function read(): string {
		$headers = getallheaders();
		$header = $GLOBALS['DEVWORX']['CFG']['CONTEXT_HEADER'];
		$server = $GLOBALS['DEVWORX']['CFG']['CONTEXT_SERVER'];
		return $headers[$header] ?? 
			$_SERVER[$server] ?? 
			$GLOBALS['DEVWORX']['CONTEXT'] ??
			$GLOBALS['DEVWORX']['FRAMEWORK'];
	}
	
	
	/**
	 * Gets the context folder name
	 *
	 * @return string
	 */	
	public static function folder(): string {
		return $GLOBALS['DEVWORX']['PATH']['CONTEXT'];
	}
	
	/**
	 * Gets the context header key
	 *
	 * @return string
	 */	
	public static function headerKey(): string {
		return $GLOBALS['DEVWORX']['CFG']['CONTEXT_HEADER'];
	}
	
	/**
	 * Gets the context server key
	 *
	 * @return string
	 */	
	public static function serverKey(): string {
		return $GLOBALS['DEVWORX']['CFG']['CONTEXT_SERVER'];
	}
	
	/**
	 * Sets a header key-value-pair
	 *
	 * @return void
	 */
	public static function setHeader(string $key, string $value): void {
		header("{$key}: {$value}");
	}

	/**
	 * Loads a specific context
	 *
	 * @param string $context The optional context to load, if empty loads the current context
	 * @return bool
	 */
	public static function load(string $context=''): bool {
		
		Performance::start(__METHOD__);
		
		$context = ucfirst( empty($context) ? self::read() : $context );
		if( !self::known($context) ){
			Performance::stop(__METHOD__);
			throw new \Exception("unknown context {$context}");
			return false;
		}
		
		self::set($context);
		
		Performance::start(__METHOD__.'::Configuration::init');
		if( !Configuration::initialize($context) ){
			Performance::stop(__METHOD__.'::Configuration');
			throw new \Exception("unable to initialize configuration for {$context}");
			return false;
		}
		Performance::stop(__METHOD__.'::Configuration::init');

		Configuration::set([
			'controller' => Frontend::getCurrentController(),
			'action' => Frontend::getCurrentAction(),
			'user' => AuthUtility::getCurrentUser(),
		],'context');

		Performance::start(__METHOD__.'::Headers');
		foreach(self::CONFIGURABLE_HEADERS as $key => $path){
			$value = Configuration::get(...$path);
			if( $value === null ) continue;
			self::setHeader($key, $value);
		}
		self::setHeader( self::headerKey(), self::get() );
		Performance::stop(__METHOD__.'::Headers');

		if( self::is('Api') ){
			self::$encoding = 'json';
			
			Performance::start(__METHOD__.'::Api');
			ApiUtility::initialize();
			Performance::stop(__METHOD__.'::Api');
			
			Performance::stop(__METHOD__);
			return true;
		}

		if( self::is('Documentation') ){
			Performance::stop(__METHOD__);
			return true;
		}
		
		Performance::start(__METHOD__.'::Session');
		SessionUtility::start();
		Performance::stop(__METHOD__.'::Session');
		
		Performance::start(__METHOD__.'::ConfigurationRender');
		Configuration::render();
		Performance::stop(__METHOD__.'::ConfigurationRender');
		
		Performance::stop(__METHOD__);
		return true;
	}
}

?>