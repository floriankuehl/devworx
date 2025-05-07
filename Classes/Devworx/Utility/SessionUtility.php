<?php

namespace Devworx\Utility;

class SessionUtility {
	
	
	/**
	 * Starts a session
	 *
	 * @return bool
	 */
	public static function start(): bool {
		return session_start();
	}
	
	/**
	 * Stops the current session
	 *
	 * @return bool
	 */
	public static function stop(): bool {
		return session_destroy();
	}
	
	/**
	 * Checks if a session has an id
	 *
	 * @param string $sessionID Optional session id
	 * @return bool
	 */
	public static function active(string $sessionID=null): bool {
		return !empty( session_id($sessionID) );
	}
	
	/**
	 * Checks if a key is present in $_SESSION
	 *
	 * @param string $key The key to retrieve
	 * @return bool
	 */
	public static function has(string $key): bool {
		return ArrayUtility::has($_SESSION,$key);
	}
	
	/**
	 * Retrieves a value from the $_SESSION
	 *
	 * @param string $key The key to retrieve
	 * @param mixed $fallback The fallback value if the key does not exist
	 * @return mixed
	 */
	public static function get(string $key=null,mixed $fallback=null): mixed {
		return ArrayUtility::key($_SESSION,$key,$fallback);
	}
	
	/**
	 * Retrieves a value from the $_SESSION
	 *
	 * @param string $key The key to retrieve
	 * @param mixed $value The value to set
	 * @return void
	 */
	public static function set(string $key,mixed $value): void {
		$_SESSION[$key] = $value;
	}
	
	/**
	 * Deletes a value from the $_SESSION or clears the whole array
	 *
	 * @param string $key The key to retrieve
	 * @return void
	 */
	public static function unset(string $key=null): void {
		if( is_null($key) )
			$_SESSION = [];
		if( self::has($key) )
			unset($_SESSION[$key]);
	}
}

?>
