<?php
namespace Devworx;

use \Devworx\Caches;
use \Devworx\Renderer\ConfigurationRenderer;
use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\FileUtility;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\DebugUtility;

/**
 * A helper class for containing and maintaining configurations
 */
class Configuration {
	
	/** @var array $config Storage array for configurations */
	static $config = null;
	
	/** @var array $cache The configuration cache instance */
	static $cache = null;

	/**
	 * Loads a JSON configuration from a file
	 * 
	 * @param string $fileName The file to load
	 * @return bool
	 */
	public static function load(string $context): bool {
		self::$config = self::$cache->get($context);
		return self::ready();
	}

	/**
	 * Saves a configuration array to a file
	 * 
	 * @param string $fileName The file to load
	 * @param bool $overwrite Allows overwriting
	 * @return bool
	 */
	public static function save( string $fileName, int $options=JSON_PRETTY_PRINT, bool $overwrite=true ): bool {
		if( is_null(self::$config) || empty(self::$config) ) return true;
		return FileUtility::setJson( $fileName, self::$config, $options, $overwrite );
	}

	/**
	 * Gets a configuration value based on a key path
	 * 
	 * @param array $path The key path
	 * @return mixed
	 */
	public static function get(...$path): mixed {
		return ArrayUtility::keys(self::$config,...$path);
	}

	/**
	 * Sets a configuration value based on a key path.
	 * 
	 * @param mixed $value The value to set
	 * @param array $path The key path
	 * @return bool
	 */
	public static function set($value, ...$path): bool {
		if (empty($path)) return false;
		return ArrayUtility::set( self::$config, $value, ...$path );
	}
	
	/**
	 * checks for an array on a config path
	 *
	 * @param array $path the config key segments
	 * @return bool
	 */
	public static function isArray(...$path): bool {
		$config = self::get(...$path);
		return is_array($config);
	}
	
	/**
	 * checks for a filled array on a config path
	 *
	 * @param array $path the config key segments
	 * @return bool
	 */
	public static function isFilledArray(...$path): bool {
		$config = self::get(...$path);
		return is_array($config) && !empty($config);
	}
	
	/**
	 * checks for a empty array on a config path
	 *
	 * @param array $path the config key segments
	 * @return bool
	 */
	public static function isEmptyArray(...$path): bool {
		$config = self::get(...$path);
		return is_array($config) && empty($config);
	}
	
	/**
	 * checks for an integer on a config path
	 *
	 * @param array $path the config key segments
	 * @return bool
	 */
	public static function isInt(...$path): bool {
		$config = self::get(...$path);
		return is_int($config);
	}
	
	/**
	 * checks for a float on a config path
	 *
	 * @param array $path the config key segments
	 * @return bool
	 */
	public static function isFloat(...$path): bool {
		$config = self::get(...$path);
		return is_float($config);
	}
	
	/**
	 * checks for an object on a config path
	 *
	 * @param array $path the config key segments
	 * @return bool
	 */
	public static function isObject(...$path): bool {
		$config = self::get(...$path);
		return is_object($config);
	}
	
	/**
	 * checks for a callable on a config path
	 *
	 * @param array $path the config key segments
	 * @return bool
	 */
	public static function isCallable(...$path): bool {
		$config = self::get(...$path);
		return is_callable($config);
	}
	
	/**
	 * checks for a bool on a config path
	 *
	 * @param array $path the config key segments
	 * @return bool
	 */
	public static function isBool(...$path): bool {
		$config = self::get(...$path);
		return is_bool($config);
	}
	
	/**
	 * checks if the configuration array is populated
	 *
	 * @return bool
	 */
	public static function ready(): bool {
		return !( ( self::$config === null ) || empty(self::$config) );
	}
		
	/**
	 * renders the current configuration with the configuration renderer
	 *
	 * @return bool
	 */
	public static function render(): bool {
		if( self::ready() ){
			$renderer = new ConfigurationRenderer();
			self::$config = $renderer->render( self::$config );
			return true;
		}
		return false;
	}
	
	/**
	 * initializes the configuration
	 *
	 * @param string $context the cached context name
	 * @return bool
	 */
	public static function initialize(string $context): bool {
			
		self::$cache = Caches::get('Configuration');
		if( self::$cache === null ){ 
			throw new \Exception('Configuration cache is empty');
			return false;
		}
		
		$globalContext = $GLOBALS['DEVWORX']['CONTEXT'];
		if( empty($context) )
			$context = $globalContext === '' ? $GLOBALS['DEVWORX']['FRAMEWORK'] : $globalContext;
		
		if( self::load( $context ) )
			return true;
		
		trigger_error("Context configuration {$context} could not be loaded from cache",E_USER_WARNING);
		return false;
		
		
	}
}
?>
