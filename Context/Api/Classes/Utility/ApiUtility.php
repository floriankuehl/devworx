<?php

namespace Api\Utility;

use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\DebugUtility;

use \Devworx\Frontend;
use \Devworx\Configuration;

class ApiUtility {

	public static $debug = !true;
	const SELFSIGNED = true;

	/** 
	 * Checks if the HEADER_KEY is provided in the request header
	 *
	 * @return bool
	 */
	public static function hasKey(): bool {
		return Frontend::hasHeader( $GLOBALS['DEVWORX']['KEYS']['CONTEXT_LOGIN'] );
	}

	/** 
	 * Reads the HEADER_KEY provided in the request header 
	 *
	 * @return string|null
	 */
	public static function getKey(): ?string {
		return ArrayUtility::key( Frontend::$header, $GLOBALS['DEVWORX']['KEYS']['CONTEXT_LOGIN']);
	}

	/** 
	 * Builds a header string by $key and $value
	 *
	 * @param string $key The header field
	 * @param string $value The header field value
	 * @return string
	 */
	public static function getHeaderString(string $key,string $value): string {
		return empty($key) ? $key : "{$key}: {$value}";
	}

	/** 
	 * Sets a header Key-Value-Pair
	 *
	 * @param string $key The header field
	 * @param string $value The header field value
	 * @return bool
	 */
	public static function setHeader(string $key,string $value): bool {
		header( self::getHeaderString($key,$value) );
		return true;
	}

	/** 
	 * Sets an array of headers
	 *
	 * @param array $headers The headers to set
	 * @return bool
	 */
	public static function setHeaders(array $headers): bool {
		$result = true;
		foreach($headers as $key=>$value)
			$result = $result && self::setHeader($key,$value);
		return $result;
	}

	/** 
	 * Initializes the API by setting neccessary headers
	 *
	 * @return bool
	 */
	public static function initialize(): bool {
		$contentType = Configuration::get('head','metaHttpEquiv','Content-Type');
		$charset = Configuration::get('charset');
		return self::setHeaders([
			'Accept' => $contentType,
			'Content-Type' => "{$contentType};charset={$charset}",
			'Keep-Alive' => 'timeout=5, max=100',
		]);
	}

	/** 
	 * Builds a header array for the current user
	 *
	 * @return array
	 */
	public static function getHeader(): array {
		return [
			self::getHeaderString( 
				'Content-Type', 
				Configuration::get('head','metaHttpEquiv','Content-Type') . ';charset=' . Configuration::get('charset')
			),
			self::getHeaderString( 
				$GLOBALS['DEVWORX']['KEYS']['CONTEXT_HEADER'], 
				Frontend::$context
			),
			self::getHeaderString( 
				$GLOBALS['DEVWORX']['KEYS']['CONTEXT_LOGIN'], 
				Configuration::get('user','login') 
			),
		];
	}

	/** 
	 * Builds a url for a given controller action pair with additional arguments
	 *
	 * @param string $controller The target controller
	 * @param string $action The target controller action
	 * @param array $arguments The additional arguments
	 * @return string
	 */
	public static function getUrl(string $controller,string $action,array $arguments=null): string {
		$config = Configuration::get('system');
		$query = [
			$config['controllerArgument'] => $controller,
			$config['actionArgument'] => $action
		];
		if( isset($arguments) && !empty($arguments) ){
			$query = ArrayUtility::combine($query,$arguments);
		}
		return implode('',[
			$_SERVER['REQUEST_SCHEME'],
			'://',
			$_SERVER['HTTP_HOST'],
			$_SERVER['SCRIPT_NAME'],
			'?',
			(is_null($query) ? '' : http_build_query($query))
		]);
	}


	/** 
	 * Performs a general request to a given url with a given method and a body
	 *
	 * @param string $method get, post, put, ...
	 * @param string $url the fully sanitized url
	 * @param array|string|null $body the request body
	 * @param bool $raw Flag to determine if the result of the request should be undecoded
	 * @return string|array
	 */
	protected static function request(
		string $method,
		string $url,
		mixed $body=null,
		bool $raw=false
	): string|array {
		$options = [
			CURLOPT_URL => $url,
			CURLOPT_CUSTOMREQUEST => strtoupper($method),
			CURLOPT_HTTPHEADER => self::getHeader(),
			CURLOPT_RETURNTRANSFER => TRUE,
			//CURLOPT_CAINFO => self::CAINFO,
			CURLOPT_SSL_VERIFYPEER => self::SELFSIGNED ? 0 : 1
		];
		if( isset($body) ){
			$options[CURLOPT_POSTFIELDS] = is_array($body) ? json_encode($body) : $body;
		}
		
		$ch = curl_init();
		curl_setopt_array($ch,$options);
		
		$result = curl_exec($ch);

		if( self::$debug ){
			$json = json_decode($result,true);
			echo DebugUtility::var_dump([
				'method' => $method,
				'url'=>$url,
				'options' => $options,
				'result' => $result,
				'json'=>$json,
				'encoded' => json_encode($json,true),
				'error' => curl_error($ch),
			],__CLASS__,__METHOD__,__LINE__);
		}

		curl_close($ch);

		return $raw ? $result : json_decode($result,true);
		
	}

	/** 
	 * Performs a GET-Request to a given controller action pair with additional arguments
	 *
	 * @param string $controller The target controller
	 * @param string $action The target controller action
	 * @param array $arguments The additional arguments
	 * @param bool $raw Flag to determine if the result of the request should be undecoded
	 * @return string|array
	 */
	public static function GET(
		string $controller,
		string $action,
		array $arguments=null,
		bool $raw=false
	): string|array {
		return self::request( 'GET', self::getUrl($controller,$action,$arguments), null, $raw );
	}

	/** 
	 * Performs a POST-Request to a given controller action pair with additional arguments
	 *
	 * @param string $controller The target controller
	 * @param string $action The target controller action
	 * @param array $arguments The payload for the request
	 * @param bool $raw Flag to determine if the result of the request should be undecoded
	 * @return string|array
	 */
	public static function POST(
		string $controller,
		string $action,
		array $arguments=null,
		bool $raw=false
	){
		return self::request('POST',self::getUrl($controller,$action),$arguments,$raw);
	}
	
	/** 
	 * Performs a PUT-Request to a given controller action pair with additional arguments
	 *
	 * @param string $controller The target controller
	 * @param string $action The target controller action
	 * @param array $arguments The payload for the request
	 * @param bool $raw Flag to determine if the result of the request should be undecoded
	 * @return string|array
	 */
	public static function POST(
		string $controller,
		string $action,
		array $arguments=null,
		bool $raw=false
	){
		return self::request('PUT',self::getUrl($controller,$action),$arguments,$raw);
	}
}
