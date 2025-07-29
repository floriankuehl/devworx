<?php

namespace Devworx;

use \Devworx\Devworx;
use \Devworx\Frontend;
use \Devworx\Configuration;
use \Devworx\Performance;

use \Devworx\Utility\ArrayUtility;
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
		
		$context = ucfirst( empty($context) ? Devworx::scanContext() : $context );
		if( !Devworx::knownContext($context) ){
			Performance::stop(__METHOD__);
			
			echo \Devworx\Utility\DebugUtility::var_dump(Devworx::contexts());
			
			trigger_error("unknown context {$context}",E_USER_ERROR);
			return false;
		}
		
		Devworx::setContext($context);
		
		Performance::start(__METHOD__.'::Configuration::init');
		if( !Configuration::initialize($context) ){
			Performance::stop(__METHOD__.'::Configuration');
			trigger_error("unable to initialize configuration for {$context}",E_USER_ERROR);
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
		self::setHeader( Devworx::headerKey(), Devworx::context() );
		Performance::stop(__METHOD__.'::Headers');

		if( Devworx::isContext('Api') ){
			self::$encoding = 'json';
			
			Performance::start(__METHOD__.'::Api');
			ApiUtility::initialize();
			Performance::stop(__METHOD__.'::Api');
			
			Performance::stop(__METHOD__);
			return true;
		}

		if( Devworx::isContext('Documentation') ){
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