<?php

namespace Devworx\Utility;

use \Devworx\Devworx;

class GeneralUtility {
  
	/**
	 * Reads data from the $_GET array with a fallback value
	 *
	 * @param string $key The key to read. if null, the $_GET array is returned
	 * @param string $default The fallback value
	 * @return mixed
	 */
	static function Get(string $key=null,string $default=null){
		return ArrayUtility::key($_GET,$key,$default);
	}

	/**
	 * Reads data from the $_POST array with a fallback value
	 *
	 * @param string $key The key to read. if null, the $_POST array is returned
	 * @param string $default The fallback value
	 * @return mixed
	 */
	static function Post(string $key=null,string $default=null){
		return ArrayUtility::key($_POST,$key,$default);
	}

	/**
	 * Reads data from the $_REQUEST array with a fallback value
	 *
	 * @param string $key The key to read. if null, the $_REQUEST array is returned
	 * @param string $default The fallback value
	 * @return mixed
	 */
	static function Request(string $key=null,string $default=null){
		return ArrayUtility::key($_REQUEST,$key,$default);
	}

	/**
	 * Reads data from the $_COOKIE array with a fallback value
	 *
	 * @param string $key The key to read. if null, the $_COOKIE array is returned
	 * @param string $default The fallback value
	 * @return mixed
	 */
	static function Cookie(string $key=null,string $default=null){
		return ArrayUtility::key($_COOKIE,$key,$default);
	}

	/**
	 * Reads data from the $_SESSION array with a fallback value
	 *
	 * @param string $key The key to read. if null, the $_SESSION array is returned
	 * @param string $default The fallback value
	 * @return mixed
	 */
	static function Session(string $key=null,string $default=null){
		return ArrayUtility::key($_SESSION,$key,$default);
	}

	/**
	* Creates an instance of a given class name
	*
	* @param string $className The FQCN for the new instance
	* @param array $args The arguments for the constructor
	* @return object|null
	*/
	static function makeInstance(string $className,...$args): ?object {
		return class_exists($className) ? new $className(...$args) : null;
	}
  
    /**
	 * Loads an instance of a given connected classname
	 * usage: loadA( 'Controller', 'User', 'Devworx' )
	 * returns: \Devworx\Controller\UserController
	 *
	 * usage: loadA( 'Utility', 'Debug', 'Devworx' )
	 * returns: \Devworx\Utility\DebugUtility
	 *
	 * usage: loadA( 'Repository', 'User', 'Devworx' )
	 * returns: \Devworx\Repository\UserRepository
	 *
	 * usage: loadA( 'Model', 'User', 'Devworx' )
	 * returns: \Devworx\Model\UserModel
	 *
	 * @param string $folder the folder / namespace
	 * @param string $name the short name of the class without the foldername
	 * @param string $context allows access to other contexts
	 * @return object|null
	 */
	public static function make(string $folder,string $name,string $context='',...$args){
		$folder = ucfirst($folder);
		$name = ucfirst($name);
		$context = ucfirst( empty($context) ? Devworx::context() : $context );
		return self::makeInstance(
			"\\{$context}\\{$folder}\\{$name}{$folder}",
			...$args
		);
	}
}
