<?php

namespace Devworx;

use Devworx\Enums\KeyName;

class Devworx {
	
	/**
	 * retrieves the first level global
	 *
	 * @return &array $result the reference to $GLOBALS['DEVWORX']
	 */
	private static function &setup(): array {
		return $GLOBALS[ KeyName::Global->value ];
	}
	
	/**
	 * Normalizes a string or KeyName enum to an uppercase string
	 *
	 * @param string|KeyName $key
	 * @return string
	 */
	private static function normalizeKey(string|KeyName $key): string {
		return is_string( $key ) ? strtoupper($key) : $key->value;
	}
	
	/**
	 * Normalizes a list of path segments to uppercase strings
	 *
	 * @param array<string|KeyName> $path
	 * @return array<string>
	 */
	private static function normalizePath(array $path): array {
		return array_map(fn($key) => self::normalizeKey($key), $path);
	}
	
	/**
	 * Gets a global value by path array
	 *
	 * @param array<string> $path the path into the global config
	 * @return mixed
	 */
	public static function get(string|KeyName...$path): mixed {
		$result = self::setup();
		if( empty($path) ) return $result;
		$path = self::normalizePath($path);
		foreach( $path as $key ){
			if( array_key_exists($key,$result) ){
				$result = $result[$key];
				continue;
			}
			$result = null;
			break;
		}
		return $result;
	}
	
	/**
	 * Sets a global value by path array
	 *
	 * @param mixed $value the value to set
	 * @param array<string> $path the path into the global config
	 * @return void
	 */
	public static function set(mixed $value,string|KeyName...$path): void {
		if( empty($path) ) return;
		$result = &self::setup();
		$path = self::normalizePath($path);
		while( !empty($path) ){
			$key = array_shift($path);
			
			if( empty($path) ){
				$result[$key] = $value;
				break;
			}
			
			if( !array_key_exists($key,$result) ){
				$result[$key] = [];
			}
			$result = &$result[$key];
		}
	}
	
	//ROOTLEVEL ----------------------------------------------------------
	
	/**
	 * Gets the global framework variable
	 *
	 * @return string
	 */
	public static function framework(): string {
		return self::get( KeyName::Framework );
	}
	
	/**
	 * Gets the global context variable
	 *
	 * @return string
	 */	
	public static function context(): string {
		return self::get( KeyName::Context );
	}
		
	/**
	 * Gets the global debug state
	 *
	 * @return bool
	 */
	public static function debug(): bool {
		return self::get( KeyName::Debug );
	}
	
	/**
	 * Sets the global debug state
	 *
	 * @param bool $value debug state
	 * @return void
	 */
	public static function setDebug(bool $value): void {
		self::set( $value, KeyName::Debug );
	}
	
	/**
	 * Sets the global context variable
	 *
	 * @param string $context the new context
	 * @return void
	 */	
	public static function setContext(string $context): void {
		self::set($context, KeyName::Context );
	}
	
	/**
	 * Checks if the program runs in a specific context
	 *
	 * @param string $context the context to check against
	 * @return bool
	 */
	public static function isContext(string $context): bool{
		return self::context() === $context;
	}
	
	/**
	 * Gets the global contexts list
	 *
	 * @return array
	 */	
	public static function contexts(): array {
		return self::get( KeyName::Contexts );
	}
	
	/**
	 * Checks if the program context is known
	 *
	 * @param string $context The context key to check
	 * @return bool
	 */
	public static function knownContext(string $context): bool {
		return in_array($context,self::contexts());
	}
	
	/**
	 * Sets the global contexts list
	 *
	 * @param array $contexts the list to set
	 * @return void
	 */	
	public static function setContexts(array $contexts): void {
		self::set( $contexts, KeyName::Contexts );
	}
	
	/**
	 * Gets a key name
	 *
	 * @param string|KeyName $value
	 * @return ?string
	 */
	public static function key(string|KeyName $value): ?string {
		return self::get( KeyName::Key, $value );
	}
	
	/**
	 * Gets a path name
	 *
	 * @param string|KeyName $value
	 * @return ?string
	 */
	public static function path(string|KeyName $value): ?string {
		return self::get( KeyName::Path, $value );
	}
	
	/**
	 * Gets the private root path
	 *
	 * @return string
	 */
	public static function privatePath(): ?string {
		return self::path( KeyName::Private );
	}
	
	/**
	 * Gets the public path
	 *
	 * @return string
	 */
	public static function publicPath(): ?string {
		return self::path( KeyName::Public );
	}
	
	/**
	 * Gets a global folder name
	 *
	 * @return string
	 */
	public static function folder(string|KeyName $key): ?string {
		return self::get( KeyName::Folder, $key );
	}
	
	/**
	 * Gets the global context folder
	 *
	 * @return string
	 */	
	public static function contextFolder(): string {
		return self::folder( KeyName::Context );
	}
	
	/**
	 * Gets the global cache folder name
	 *
	 * @return string
	 */	
	public static function cacheFolder(): string {
		return self::folder( KeyName::Cache );
	}
	
	/**
	 * Gets the global configuration folder name
	 *
	 * @return string
	 */	
	public static function configurationFolder(): string {
		return self::folder( KeyName::Configuration );
	}
	
	/**
	 * Gets the global classes folder name
	 *
	 * @return string
	 */	
	public static function classesFolder(): string {
		return self::folder( KeyName::Classes );
	}
	
	/**
	 * Gets the global repository folder name
	 *
	 * @return string
	 */	
	public static function repositoryFolder(): string {
		return self::folder( KeyName::Repository );
	}
	
	/**
	 * Gets the global model folder name
	 *
	 * @return string
	 */	
	public static function modelFolder(): string {
		return self::folder( KeyName::Model );
	}
	
	/**
	 * Gets the global controller folder name
	 *
	 * @return string
	 */	
	public static function controllerFolder(): string {
		return self::folder( KeyName::Controller );
	}
	
	/**
	 * Gets the global resource folder name
	 *
	 * @return string
	 */	
	public static function resourceFolder(): string {
		return self::folder( KeyName::Resource );
	}
	
	/**
	 * Gets the global script folder name
	 *
	 * @return string
	 */	
	public static function scriptFolder(): string {
		return self::folder( KeyName::Script );
	}
	
	/**
	 * Gets the global style folder name
	 *
	 * @return string
	 */	
	public static function styleFolder(): string {
		return self::folder( KeyName::Style );
	}
	
	/**
	 * Gets the context header key
	 *
	 * @return string
	 */	
	public static function headerKey(): string {
		return self::key( KeyName::ContextHeader );
	}
	
	/**
	 * Gets the context server key
	 *
	 * @return string
	 */	
	public static function serverKey(): string {
		return self::key( KeyName::ContextServer );
	}
	
	/**
	 * Gets the context login key
	 *
	 * @return string
	 */	
	public static function loginKey(): string {
		return self::keys( KeyName::ContextLogin );
	}
	
	/**
	 * Retrieves the current context with fallback to the framework name
	 *
	 * @return string
	 */
	public static function scanContext(): string {
		$headers = getallheaders();
		return $headers[ self::headerKey() ] ?? 
			$_SERVER[ self::serverKey() ] ?? 
			self::framework();
	}
	
	/**
	 * Scans for contexts in the context folder
	 *
	 * @return array $result the list of contexts
	 **/
	public static function scanContexts():array {
		
		$root = self::privatePath();
		$context = self::contextFolder();
		
		$folder = "{$root}/{$context}";
			
        if (is_dir($folder)) {
            return array_filter(
				scandir($folder),
				fn($dir) => is_dir("$folder/$dir") && !( $dir === '.' || $dir === '..' )
			);
        }
		
		trigger_error("Context folder {$folder} not found in Devworx::scanContexts",E_USER_ERROR);
		return [];
	}
	
	/**
	 * initializes the autoloader
	 *
	 * @return void
	 **/
    public static function initialize(): void
    {
		$debug = self::debug();
		$root = self::privatePath();
		$framework = self::framework();
		$context = self::contextFolder();
		$classes = self::classesFolder();
		
		// Error Handling
		ini_set('display_errors', $debug ? '1' : '0');
		ini_set('display_startup_errors', $debug ? '1' : '0');
		error_reporting( $debug ? E_ALL : 0 );
		
		self::setContexts( self::scanContexts() );
		
		require_once("{$root}/{$context}/{$framework}/{$classes}/Autoloader.php");
		spl_autoload_register([Autoloader::class,'load'], true, true);
		
		Autoloader::addContext( $framework );
		Autoloader::addAlias( 'Cascade', "{$framework}/{$classes}/Cascade" );
		foreach( self::contexts() as $context )
			Autoloader::addContext( $context );
		
		set_exception_handler( [ Utility\DebugUtility::class, 'exception' ] );
		set_error_handler([ Utility\DebugUtility::class, 'error' ]);
		
		Frontend::initialize();
    }
	
}