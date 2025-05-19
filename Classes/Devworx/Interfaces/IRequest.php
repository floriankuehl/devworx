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
  
  function Get(string $key=null): ?mixed;
  function Post(string $key=null): ?mixed;
  function Put(string $key=null): ?mixed;
  
  function getArguments(): array;
  function hasArgument(string $key): bool;
  function getArgument(string $key);
  
  function getFiles(string $key): array;
  function hasFiles(string $key): bool;
  function hasFile(string $key,int $index): bool;
  function getFile(string $key,string $subkey,int $index);
  
  
}

?>