<?php

namespace Devworx\Utility;

use \Devworx\Configuration;

class CookieUtility {

	/**
	 * Gets the cookie name from the configuration
	 *
	 * @return string
	 */
	static function Name(): string {
		return Configuration::get('cookie','name');
	}

	/**
	 * Gets the expire duration from the configuration
	 *
	 * @return int
	 */
	static function Expires(): int {
		return Configuration::get('cookie','expires');
	}

	/**
	 * Gets the sameSite flag from the configuration
	 *
	 * @return string
	 */
	static function SameSite(): string {
		return Configuration::get('cookie','sameSite');
	}

	/**
	 * Gets the folder of the current script
	 *
	 * @return string
	 */
	static function getProgramFolder(): string {
		$tokens = explode('/',$_SERVER['SCRIPT_NAME']);
		array_pop($tokens);
		return implode('/',$tokens);
	}

	/**
	 * Checks if the devworx cookie is set
	 *
	 * @return bool
	 */
	static function has(): bool {
		return array_key_exists(self::Name(),$_COOKIE);
	}

	/**
	 * Sets the devworx cookie value
	 *
	 * @param string $value The cookie value to set (user hash)
	 * @return bool
	 */
	static function set(string $value): bool {
		return empty($value) ? false : setcookie(
			self::Name(),
			$value,
			[
				'expires' => time() + self::Expires(),
				'path' => '/',
				'domain' => $_SERVER['HTTP_HOST'],
				'samesite' => self::SameSite(),
				'secure' => ( array_key_exists('HTTPS',$_SERVER) && ( $_SERVER['HTTPS'] == 'on' ) ),
			]
		);
	}

	/**
	 * Refreshs the devworx cookie
	 *
	 * @return bool
	 */
	static function refresh(): bool {
		return self::set( self::get() );
	}

	/**
	 * Gets the devworx cookie value
	 *
	 * @return string|null
	 */
	static function get(): ?string {
		return $_COOKIE[self::Name()] ?? null;
	}

	/**
	 * Unsets the devworx cookie by altering the expire date
	 *
	 * @return bool
	 */
	static function unset(): bool {
		return setcookie(
			self::Name(),
			"",
			[
				'expires' => 0,
				'path' => '/',
				'domain' => $_SERVER['HTTP_HOST'],
				'samesite' => self::SameSite(),
				'secure' => ( array_key_exists('HTTPS',$_SERVER) && ( $_SERVER['HTTPS'] == 'on' ) ),
			]
		);
	}
}

?>
