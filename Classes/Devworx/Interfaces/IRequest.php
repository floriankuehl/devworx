<?php

namespace Devworx\Interfaces;

/**
 * The interface for requests
 */
interface IRequest {
  function getArguments(): array;
  function hasArgument(string $key): bool;
  function getArgument(string $key);
  function getMethod(): string;
  function getBody(): string;
  
  function getFiles(string $key): array;
  function hasFiles(string $key): bool;
  function hasFile(string $key,int $index): bool;
  function getFile(string $key,string $subkey,int $index);
  
  function isGet(): bool;
  function isPost(): bool;
  function isPut(): bool;
}

?>