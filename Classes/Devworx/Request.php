<?php

namespace Devworx;

use \Devworx\Interfaces\IRequest;


class Request implements IRequest {
  
  protected $arguments = null;
  protected $method = null;
  
  function __construct(){
    $this->arguments = $_REQUEST;
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