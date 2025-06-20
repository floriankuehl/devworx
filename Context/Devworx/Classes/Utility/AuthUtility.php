<?php

namespace Devworx\Utility;

use \Devworx\Frontend;
use \Devworx\Database;
use \Devworx\Context;
use \Api\Utility\ApiUtility;

class AuthUtility {

	/**
	 * Creates a user hash from a username and a password
	 *
	 * @param string $userName The username
	 * @param string $password The password
	 * @return string
	 */
	public static function createUserHash(string $userName, string $password): string {
		return md5("{$userName}|{$password}");
	}

	/**
	 * Checks if a given string is MD5 and checks if a user with this login exists
	 *
	 * @param string $hash The login hash
	 * @return bool
	 */
	public static function isUserHash(string $hash): bool {
		if( StringUtility::isMd5($hash) ){
			$result = Database::prepare("SELECT COUNT(*) AS isUser FROM user WHERE login = ? LIMIT 1;",[$hash],true);
			return (bool) $result['isUser'];
		}
		return false;
	}

	/**
	 * Retrieves a user by its login hash
	 *
	 * @param string $hash The user login hash
	 * @return array|null
	 */
	public static function getUserByHash(?string $hash): ?array{
		if( is_null($hash) || empty($hash) )
			return null;
		return Database::prepare("SELECT * FROM user WHERE login = ? LIMIT 1;",[$hash],true);
	}

	/**
	 * Sets the last login timestamp of a user by hash
	 *
	 * @param string $hash The user login hash
	 * @return bool
	 */
	public static function setLastLogin(?string $hash): bool {
		if( is_null($hash) || empty($hash) )
			return false;
		$result = Database::prepare("UPDATE user SET lastLogin=CURRENT_TIMESTAMP WHERE login = ? LIMIT 1;",[$hash],true);
		return (bool)$result;
	}

	/**
	 * Retrieves the current user hash by the given context
	 *
	 * @return string|null
	 */
	public static function getStoredHash(): ?string {
		return Context::is('Api') ? 
		ApiUtility::getKey() : 
		CookieUtility::get();
	}

	/**
	 * Retrieves the current user by the stored hash
	 *
	 * @return array|null
	 */
	public static function getCurrentUser(): ?array {
		$hash = self::getStoredHash();
		return is_null($hash) ? $hash : self::getUserByHash($hash);
	}

	/**
	 * Checks if a devworx cookie exists
	 *
	 * @return bool
	 */
	public static function cookie(): bool {
		$hash = CookieUtility::get();
		return is_string($hash) && !empty($hash);
	}

	/**
	 * Performs a login based on POST data
	 *
	 * @return bool
	 */
	public static function post():bool {
		$result = $_SERVER['REQUEST_METHOD'] === 'POST';
		if( $result ){
			$result = ArrayUtility::hasValue($_POST,'username') && 
				ArrayUtility::hasValue($_POST,'password');
			if( $result ){
				$payload = self::createUserHash($_POST['username'], $_POST['password']);
				
				return self::isUserHash( $payload ) && 
					CookieUtility::set( $payload ) && 
					self::setLastLogin( $payload );
			}
		}
		return $result;
	}

	/**
	 * Performs a logout by destroying the session and the cookie
	 *
	 * @return void
	 */
	public static function lock(): void {
		SessionUtility::stop();
		CookieUtility::unset();
	}
}

?>
