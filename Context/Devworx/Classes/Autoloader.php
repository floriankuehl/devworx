<?php

namespace Devworx;

/**
 * The Autoloader class
 * Manages loading classes from different folders.
 * Scans for folders in "/KeyName::Contex"
 **/
class Autoloader
{
	/**
	 * @var array $contextMap the namespace map
	 **/
    protected static array $contextMap = [];
	
	/**
	 * @var array $classMap the class map
	 **/
    protected static array $classMap = [];
	
	/**
	 * Adds all contexts to the contextMap
	 *
	 * @param array $contexts the given contexts
	 * @return bool
	 **/
	public static function createContextMap(array $contexts): bool {
		return array_reduce(
			$contexts,
			function($acc, $context) {
				$ok = self::addContext($context);
				if (!$ok) 
					trigger_error("Autoloader: Kontext $context konnte nicht hinzugefügt werden",E_USER_ERROR);
				return $acc && $ok;
			},
			true
		);
	}
	
	/**
	 * Adds a context to the contextMap
	 *
	 * @param string $context the given context
	 * @param string $folder the context folder the namespace points to
	 * @param bool $overwrite the flag to prevent overwriting
	 * @return bool
	 **/
	public static function addContext(string $context, string $folder='', bool $overwrite=false): bool {
		if( self::knownContext($context) && !$overwrite )
			return false;
		self::$contextMap[$context] = implode('/',[ 
			Devworx::contextFolder(), 
			ucfirst($context), 
			empty($folder) ? Devworx::classesFolder() : $folder
		]);
		return true;
	}
	
	/**
	 * Adds an alias to the contextMap
	 *
	 * @param string $context the given context
	 * @param string $folder the context folder the namespace points to
	 * @param bool $overwrite the flag to prevent overwriting
	 * @return bool
	 **/
	public static function addAlias(string $context, string $folder, bool $overwrite=false): bool {
		if( self::knownContext($context) && !$overwrite )
			return false;
		self::$contextMap[$context] = implode('/',[ Devworx::contextFolder(), $folder ]);
		return true;
	}
	
	/**
	 * Checks if a context exists in the contextMap
	 *
	 * @param string $context the given context
	 * @return bool
	 **/
	public static function knownContext(string $context): bool {
		return array_key_exists($context,self::$contextMap);
	}
	
	/**
	 * sets the context map
	 *
	 * @return void
	 **/
	public static function setContextMap(array $map): void {
		foreach( $map as $k => $v )
			self::$contextMap[$k] = $v;
	}

	/**
	 * generates a filename to the php class file
	 *
	 * @return string $result the full file path in the Context-Folder
	 **/
	public static function fileName(string $context,string $name): string {
		return implode(DIRECTORY_SEPARATOR,[
			Devworx::privatePath(),
			self::$contextMap[$context],
			str_replace('\\',DIRECTORY_SEPARATOR,$name) . '.php'
		]);
	}

	/**
	 * checks if a $context $name pair is loadable
	 *
	 * @param string $context the given context
	 * @param string $name the given class name without namespace
	 * @param string $file gives out the file name
	 * @param string $class gives out the FQCN
	 *
	 * @return bool $result the combination can be loaded
	 **/
	public static function loadable(string $context, string $name, string &$file=null, string &$class=null): bool {
		$file = self::fileName($context,$name);
		$class = "\\{$context}\\{$name}";
		return self::knownContext($context) && file_exists($file);
	}
	
	/**
	 * trys to load a $context $name pair, registers loaded classes in the classMap
	 * loads file if context is known and file exists
	 *
	 * @param string $context the given context
	 * @param string $name the given class name without namespace
	 * @param string $file gives out the file name
	 * @param string $class gives out the FQCN
	 *
	 * @return bool $result the class exists
	 **/
	public static function try(string $context, string $name, ?string &$file=null, ?string &$class=null): bool {
		if( self::loadable( $context, $name, $file, $class ) ){
			require_once($file);
			if( class_exists($class,false) ){
				self::$classMap[ $class ] = $file;
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * the actual loader function
	 *
	 * @param string $className the FQCN to load
	 * @return bool
	 **/
    public static function load(string $className): bool
    {
		if( empty($className) )
			return false;
		
		$className = ltrim($className, '\\');
        $name = explode('\\', $className);
		$context = array_shift($name);
		$name = implode('\\',$name);
		
		if( empty($context) )
			return false;

		if( empty($name) ){
			if( self::try( Devworx::context(), $context ) ){
				return true;
			}
			if( self::try( Devworx::framework(), $context ) ){
				return true;
			}
			trigger_error("Autoloader: $className not found in current context nor framework",E_USER_ERROR);
			return false;
		}
		
        return self::try( $context, $name );
    }
	
	public static function loadCachedContextMap(){
		//not used atm. Overkill?
		foreach( \Devworx\Caches::get('Class')->all() as $k => $v )
			self::$contextMap[$k] = $v;
	}
}
