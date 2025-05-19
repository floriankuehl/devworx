<?php

namespace Devworx;

use \Devworx\Interfaces\IRequest;


class Request implements IRequest {
  
  protected $arguments = null;
  protected $method = null;
  protected $files = null;
  
  function __construct(){
    $this->arguments = $_REQUEST;
    $this->method = $_SERVER['REQUEST_METHOD'];
	$this->files = $_FILES;
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
  function getArgument(string $key,$fallback=null){
    if( $this->hasArgument($key) )
      return $this->arguments[$key];
    return $fallback;
  }
  
  /**
   * Gets the current upload files
   *
   * @return array
   */
  function getFiles(): array {
    return $this->files;
  }
  
  /**
   * Checks if a specific upload file list exists
   *
   * @param string $key The key of the $_FILES array
   * @return bool
   */
  function hasFiles(string $key): bool {
	if( empty($this->files) )
		return false;
	return ArrayUtility::key($this->files,$key);
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
		return ArrayUtility::isIndex($this->files['name'],$index);
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
			foreach( $this->files[$key] as $k => $v )
				$file[$k] = $v[$index];
			return $file;
		}
		
		if( ArrayUtility::has($this->files,$subkey) )
			return $this->files[$key][$subkey][$index];
		
		if( ArrayUtility::key($this->files,$key,$subkey)
      return $this->files[$key][$subkey];
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
   * @return array
   */
  function getJson(){
    return json_decode( $this->getBody(), true );
  }
  
}

?>