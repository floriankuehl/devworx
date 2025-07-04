<?php

namespace Devworx;

use \Devworx\Interfaces\IRequest;
use \Devworx\Utility\ArrayUtility;

class Request implements IRequest {
  
  protected $arguments = null;
  protected $method = null;
  
  function __construct(){
    $this->arguments = $_REQUEST;
	/* filter by plugin key? controller?_action? */
    $this->method = $_SERVER['REQUEST_METHOD'];
  }
  
  /**
   * The getter for the current method
   *
   * @return string
   */
  function getMethod(): string {
    return $this->method;
  }
  
  /**
   * Checks if the method is GET
   *
   * @return bool
   */
  function isGet(): bool {
    return $this->method === 'GET';
  }
  
  /**
   * Checks if the method is POST
   *
   * @return bool
   */
  function isPost(): bool {
    return $this->method === 'POST';
  }
  
  /**
   * Checks if the method is PUT
   *
   * @return bool
   */
  function isPut(): bool {
    return $this->method === 'PUT';
  }
  
  /**
   * Gets a _GET variable
   *
   * @param string $key optional key
   * @return mixed
   */
  function Get(string $key=null): mixed {
	  return ArrayUtility::key($_GET,$key);
  }
  
  /**
   * Sets a _GET variable
   *
   * @param string $key key
   * @param mixed $value
   * @return void
   */
  function setGet(string $key,mixed $value): void {
	  $_GET[$key] = $value;
  }
  
  /**
   * Gets a _POST variable
   *
   * @param string $key optional key
   * @return mixed
   */
  function Post(string $key=null): mixed {
	  return ArrayUtility::key($_POST,$key);
  }
  
  /**
   * Sets a _POST variable
   *
   * @param string $key key
   * @param mixed $value
   * @return void
   */
  function setPost(string $key,mixed $value): void {
	  $_POST[$key] = $value;
  }
  
  /**
   * Gets a _PUT variable
   *
   * @param string $key optional key
   * @return mixed
   */
  function Put(string $key=null): mixed {
	  return ArrayUtility::key($_PUT,$key);
  }
  
  /**
   * Sets a _PUT variable
   *
   * @param string $key key
   * @param mixed $value
   * @return void
   */
  function setPut(string $key,mixed $value): void {
	  $_PUT[$key] = $value;
  }
  
  /**
   * Gets a _REQUEST variable
   *
   * @param string $key optional key
   * @return mixed
   */
  function Request(string $key=null): mixed {
	  return ArrayUtility::key($_REQUEST,$key);
  }
  
  /**
   * Sets a _REQUEST variable
   *
   * @param string $key key
   * @param mixed $value
   * @return void
   */
  function setRequest(string $key,mixed $value): void {
	  $_REQUEST[$key] = $value;
  }
  
  /**
   * Gets the current request arguments
   *
   * @return array
   */
  function getArguments(): array {
    return $this->arguments;
  }
  
  /**
   * Checks if a specific argument exists
   *
   * @param string $key The name of the argument
   * @return bool 
   */
  function hasArgument(string $key): bool {
    return array_key_exists($key,$this->arguments);
  }
  
  /**
   * Retrieves an argument if it exists, else returns fallback
   *
   * @param string $key The name of the argument
   * @param mixed $fallback The fallback value
   * @return mixed
   */
  function getArgument(string $key,$fallback=null): mixed {
    return $this->arguments[$key] ?? $fallback;
  }
  
  /**
   * Sets an argument
   *
   * @param string $key key
   * @param mixed $value value
   * @return void
   */
  function setArgument(string $key,mixed $value): void {
    $this->arguments[$key] = $value;
  }
  
  /**
   * Gets the current upload files
   *
   * string $key
   * @return ?array
   */
  function getFiles(string $key): ?array {
    return ArrayUtility::key($_FILES,$key);
  }
  
  /**
   * Checks if a specific upload file list exists
   *
   * @param string $key The key of the $_FILES array
   * @return bool
   */
  function hasFiles(string $key): bool {
	return ArrayUtility::has($_FILES,$key);
  }
  
  /**
   * Checks if a specific upload file exists
   *
   * @param string $key The name of the file list
   * @param int $index The index of the file row
   * @return bool
   */
  function hasFile(string $key,int $index): bool {
	  if( $this->hasFiles( $key ) ){
		return ArrayUtility::isIndex($_FILES[$key]['name'],$index);
	  }
	  return false;
  }
  
  /**
   * Retrieves a file if it exists
   * if $subkey is empty, full data is returned as an associative array
   *
   * @param string $key The name of the argument
   * @param string $subkey The file optional info key (e.g. 'name').
   * @param int $index The index of the file row
   * @return mixed
   */
  function getFile(string $key,string $subkey,int $index): mixed {
    if( $this->hasFile($key,$index) ){
		if( empty($subkey) ){
			$file = [];
			foreach( $_FILES[$key] as $k => $list )
				$file[$k] = $list[$index];
			return $file;
		}
		
		if( ArrayUtility::has($_FILES[$key],$subkey) )
			return $_FILES[$key][$subkey][$index];
	}
	return null;
  }
  
  /**
   * Retrieves the current request body from php://input
   *
   * @return string
   */
  function getBody(): string {
    return file_get_contents('php://input');
  }
  
  /**
   * Retrieves the current request body from php://input as JSON
   *
   * @return mixed
   */
  function getJson(): mixed {
    return json_decode( $this->getBody(), true );
  }
  
}

?>