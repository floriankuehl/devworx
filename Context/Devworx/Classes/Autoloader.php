<?php

namespace Devworx;

/**
 * The Autoloader class
 * Manages loading classes from different folders.
  * Scans for folders in "devworx/Context"
 **/
class Autoloader
{
	/**
	 * @var string $root the root folder
	 **/
    protected static string $root;
	
	/**
	 * @var string $context the context folder
	 **/
    protected static string $context;
	
	/**
	 * @var array $contextMap the namespace map
	 **/
    protected static array $contextMap = [];
	
	/**
	 * initializes the autoloader
	 *
	 * @return void
	 **/
    public static function initialize(): void
    {
		self::$root = $GLOBALS['DEVWORX']['PATH']['ROOT'];
		self::$context = $GLOBALS['DEVWORX']['PATH']['CONTEXT'];
		
		self::addContext('Devworx');
		$GLOBALS['DEVWORX']['CONTEXTS'] = self::scanContexts();
    }
	
	public static function createContextMap(array $contexts): array {
		return array_walk(
			$contexts,
			fn($context)=>self::addContext($context)
		);
	}
	
	public static function addContext(string $context): void {
		self::$contextMap[$context] = implode('/',[ self::$context, ucfirst($context), 'Classes' ]);
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

	public static function scanContexts():array{
		$folder = self::$root . DIRECTORY_SEPARATOR . self::$context;
        if (is_dir($folder)) {
            return array_filter(
				scandir($folder),
				fn($dir)=>is_dir("$folder/$dir") && !( $dir === '.' || $dir === '..' )
			);
        }
		throw new \Exception("Folder {$folder} not found in Autoloader::scanContexts");
		return [];
	}

	/**
	 * the actual loader function
	 *
	 * @param string $className the FQCN to load
	 * @return bool
	 **/
    public static function load(string $className): bool
    {
        $className = ltrim($className, '\\');
        $parts = explode('\\', $className);

        if (count($parts) < 2) return false;

        $context = array_shift($parts);
        if (!isset(self::$contextMap[$context])){
			throw new \Exception("Context {$context} not found in Autoloader::contextMap");
			return false;
		}
		
		$relativePath = implode(DIRECTORY_SEPARATOR, $parts) . '.php';
        $basePath = self::$contextMap[$context];
        $fullPath = $GLOBALS['DEVWORX']['PATH']['ROOT'] . DIRECTORY_SEPARATOR . $basePath . DIRECTORY_SEPARATOR . $relativePath;

		if (file_exists($fullPath)) {
            require_once $fullPath;
            return true;
        }

        //throw new \Exception("Autoloader: $className not found in path $fullPath");
        return false;
    }
	
	public static function loadCachedContextMap(){
		foreach( \Devworx\Caches::get('Class')->all() as $k => $v )
			self::$contextMap[$k] = $v;
	}
}
