<?php

namespace Devworx;

use \Devworx\Configuration;
use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\PathUtility;

class Redirect {
	
	/**
	 * Redirects the current location by replacing the Location header
	 *
	 * @param string $url The new location
	 * @param int $status The HTTP status (defaults to 301)
	 * @return void
	 */
	static function to(string $url,int $status=301): void {
		header("Location: {$url}",true,$status);
	}
	
	/**
	 * Redirects to a controller action with optional GET-arguments
	 *
	 * @param string $controller The controller name
	 * @param string $action The action name
	 * @param array|null $arguments The additional arguments
	 * @param string|null $anchor The optional anchor of the url
	 * @param int $status The HTTP status (defaults to 301)
	 * @return void
	 */
	public static function action( 
		string $controller, 
		string $action, 
		array $arguments=null, 
		string $anchor=null, 
		int $status=301 
	): void {
		self::to( PathUtility::action( $controller, $action, $arguments, $anchor ), $status );
	}
	
	/**
	 * Redirects to any url with GET-arguments and anchor
	 *
	 * @param string $uri The base uri
	 * @param array|null $arguments The additional arguments
	 * @param string|null $anchor The optional anchor of the url
	 * @return void
	 */
	public static function uri( string $uri, array $arguments=null, string $anchor=null ): void {
		$formData = $arguments ?? [];
		$fullUri = "{$uri}?" . http_build_query($formData) . ( is_null($anchor) ? '' : "#{$anchor}" );
		self::to( $fullUri  );
	}

	/**
	 * Redirects to the default controller action
	 *
	 * @param string $controller The controller name
	 * @param string $action The action name
	 * @return void
	 */
	public static function default(): void {
		self::action(
			Configuration::get('system','defaultController'),
			Configuration::get('system','defaultAction')
		);
	}

	/**
	 * Redirects back to the referrer
	 *
	 * @return void
	 */
	public static function referrer(){
		self::to( $_SERVER['HTTP_REFERER'] );
	}
	
}