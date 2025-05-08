<?php
namespace Devworx;

/**
 * A helper class for containing and maintaining configurations
 */
class ConfigManager {

	/** @var array Storage array for configurations */
	static $config = null;

	/**
	 * Loads a JSON configuration from a file
	 * 
	 * @param string $fileName The file to load
	 * @return bool
	 */
	public static function loadConfig(string $fileName): bool {
		if( is_file($fileName) ){
		  self::$config = json_decode( file_get_contents( $fileName ), true );
		  return is_array(self::$config);
		}
		return false;
	}

	/**
	 * Saves a configuration array to a file
	 * 
	 * @param string $fileName The file to load
	 * @param bool $overwrite Allows overwriting
	 * @return bool
	 */
	public static function saveConfig(string $fileName,bool $overwrite=true): bool {
		if( is_file($fileName) && !$overwrite )
		  return false;
		$content = json_encode( self::$config, JSON_PRETTY_PRINT );
		return file_put_contents($fileName,$content);
	}

	/**
	 * Gets a configuration value based on a key path
	 * 
	 * @param array $path The key path
	 * @return string|array|int|float|null
	 */
	public static function getConfig(...$path): string|array|int|float|null {
		$result = self::$config;
		forEach( $path as $key ){
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
	 * Sets a configuration value recursively based on a key path. Can create trees.
	 * 
	 * @param array|null $branch The current configuration branch
	 * @param mixed $value The value to set
	 * @param array $path The key path
	 * @return bool
	 */
	public static function setConfig(?array $branch, $value, ...$path): bool {
		$pointer = ( 
		  is_null($branch) ? 
		  self::$config : 
		  $branch 
		);
		$key = array_shift($path);

		if( empty($key) )
		  return false;

		if( array_key_exists($key,$pointer) ){
		  if( empty($path) ){
			$pointer[$key] = $value;
			return true;
		  }
		  return self::setConfig($pointer[$key], $value, ...$path);
		}

		if( empty($path) ){
		  $pointer[$key] = $value;
		  return true;
		}

		$pointer[$key] = [];

		return self::setConfig($pointer,$value,...$path);
	}
}
?>
