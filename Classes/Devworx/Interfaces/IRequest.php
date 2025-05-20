<?php

namespace Devworx\Interfaces;

/**
 * The interface for requests
 */
interface IRequest {
  function getMethod(): string;
  function getBody(): string;
  
  function isGet(): bool;
  function isPost(): bool;
  function isPut(): bool;
  
  function Get(string $key=null): mixed;
  function Post(string $key=null): mixed;
  function Put(string $key=null): mixed;
  
  function setGet(string $key, mixed $value): void;
  function setPost(string $key, mixed $value): void;
  function setPut(string $key, mixed $value): void;
  function setRequest(string $key, mixed $value): void;
  
  function getArguments(): array;
  function hasArgument(string $key): bool;
  function getArgument(string $key): mixed;
  function setArgument(string $key,mixed $value): void;
  
  function getFiles(string $key): ?array;
  function hasFiles(string $key): bool;
  function hasFile(string $key,int $index): bool;
  function getFile(string $key,string $subkey,int $index);
  
  
}

?>