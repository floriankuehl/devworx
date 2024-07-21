<?php

namespace Devworx;

interface IRequest {
  function getArguments(): array;
  function hasArgument(string $key): bool;
  function getArgument(string $key);
  function getMethod(): string;
  
  function isGet(): bool;
  function isPost(): bool;
  function isPut(): bool;
}

class Request {
  
  protected 
    $arguments = null,
    $method = null;
  
  function __construct(){
    $this->arguments = $_REQUEST;
    $this->method = $_SERVER['REQUEST_METHOD'];
  }
  
  function getMethod(): string {
    return $this->method;
  }
  
  function isGet(): bool {
    return $this->method === 'GET';
  }
  
  function isPost(): bool {
    return $this->method === 'POST';
  }
  
  function isPut(): bool {
    return $this->method === 'PUT';
  }
  
  function getArguments(): array {
    return $this->arguments;
  }
  
  function hasArgument(string $key): bool {
    return array_key_exists($key,$this->arguments);
  }
  
  function getArgument(string $key){
    if( $this->hasArgument($key) )
      return $this->arguments[$key];
    return null;
  }
  
}

?>