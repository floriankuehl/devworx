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
  
  function isGet(): bool;
  function isPost(): bool;
  function isPut(): bool;
}

?>