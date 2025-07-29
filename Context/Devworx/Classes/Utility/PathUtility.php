<?php

namespace Devworx\Utility;

use Devworx\Frontend;
use Devworx\Devworx;
use Devworx\Enums\KeyName;
use Devworx\Configuration;

class PathUtility {
	
	const PATHGLUE = '/';
	/**
	 * Builds a path based on a specific devworx path key
	 *
	 * @param string $rootKey The key in the global devworx path configuration
	 * @param array $segments The path segments
	 * @return string
	 */
	public static function build(string $rootKey,...$segments){
		$segments = array_map(fn($s)=>trim($s,self::PATHGLUE),$segments);
			return implode(self::PATHGLUE,[
			Devworx::path($rootKey),
			...$segments
		]);
	}

	/**
	 * Gets a root folder or file path based on given segments
	 *
	 * @param array $segments The path segments
	 * @return string
	 */
	public static function private(...$segments): string {
		return self::build( KeyName::Private->value,...$segments);
	}
	
	/**
	 * Gets a root folder or file path based on given segments
	 *
	 * @param array $segments The path segments
	 * @return string
	 */
	public static function realPrivate(...$segments): string {
		return realpath( self::private(...$segments) );
	}
	
	/**
	 * Gets the relative public folder or file path based on given segments
	 *
	 * @param array $segments The path segments
	 * @return string
	 */
	public static function public(...$segments): string {
		return self::build( KeyName::Public->value,...$segments);
	}
	
	/**
	 * Gets the real path of a public folder or file path based on given segments
	 *
	 * @param array $segments The path segments
	 * @return string
	 */
	public static function realPublic(...$segments): string {
		return realpath( self::public(...$segments) );
	}

	/**
	 * Gives debug information about a folder or file path based on given segments
	 *
	 * @param array $segments The path segments
	 * @return array
	 */
	public static function debug(...$segments): array {
		$path = self::private(...$segments);
		return [
			'list' => $segments,
			'path' => $path,
			'real' => realpath($path)
		];
	}

	/**
	* Creates a relative path between two paths
	*
	* @param string $from the source path
	* @param string $to the target path
	* @return string
	*/
	public static function between(string $from, string $to): string {
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
	public static function relative(string $path): string {
		return self::between($_SERVER['DOCUMENT_ROOT'],$path);
	}

	/**
	* Generates a context based path
	*
	* @param string $context The given context
	* @param array $segments following segments
	* @return string
	*/
	public static function context(string $context,...$segments): string {
		return self::private( 
			Devworx::contextFolder(), 
			ucfirst($context), 
			...$segments 
		);
	}

	/**
	 * Gets a context based public resource path
	 *
	 * @param string $context The given context
	 * @param array $segments The path segments
	 * @return string
	 */
	public static function resource(string $context,...$segments): string {
		return '/' . self::public( strtolower( Devworx::resourceFolder() ), strtolower($context), ...$segments);
	}
	
	/**
	 * Gets a path to a cache folder
	 *
	 * @param array $segments The path segments
	 * @return string
	 */
	public static function cache(...$segments): string {
		return self::private( 
			Devworx::cacheFolder(), 
			...$segments
		);
	}
	
	/**
	 * Gets a path to a context configuration folder
	 *
	 * @param array $segments The path segments
	 * @return string
	 */
	public static function configuration(string $context,...$segments): string {
		return self::context( 
			$context,
			Devworx::configurationFolder(),
			...$segments
		);
	}

	/**
	 * Generates a context based path
	 *
	 * @param array $segments following segments
	 * @return string
	 */
	public static function currentContext(...$segments): string {
		return self::context( Devworx::context(), ...$segments );
	}
  
	/**
	 * Gets a current context based resource path
	 *
	 * @param array $segments The path segments
	 * @return string
	 */
	public static function currentResource(...$segments): string {
		return self::resource( Devworx::context(),...$segments);
	}
	
	/**
	 * Gets the relative private path based on a configuration
	 *
	 * @param array $path The configuration path
	 * @param array $segments additional path segments
	 * @return string
	 */
	public static function privateConfig(array $path,...$segments): string {
		return self::private( Configuration::get(...$path), ...$segments );
	}
	
	/**
	 * Gets the relative public path based on a configuration
	 *
	 * @param array $path The configuration path
	 * @param array $segments additional path segments
	 * @return string
	 */
	public static function publicConfig(array $path,...$segments): string {
		return self::public( Configuration::get(...$path), ...$segments );
	}
	
	/**
	 * Gets the path based on the current context and a configuration path
	 *
	 * @param array $path The configuration path
	 * @param array $segments additional path segments
	 * @return string
	 */
	public static function currentConfig(array $path,...$segments): string {
		return self::currentContext( Configuration::get(...$path), ...$segments );
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
	public static function action(string $controller,string $action,array $arguments=null,string $anchor=null): string {
		$formData = ArrayUtility::combine(
			[
				Configuration::get('system','controllerArgument') => $controller,
				Configuration::get('system','actionArgument') => $action
			],
			$arguments
		);
		return "?" . http_build_query($formData) . ( is_null($anchor) ? '' : "#{$anchor}" );
	}
}