<?php

namespace Devworx\Interfaces;

/**
 * The interface for requests
 */
interface IRequest {
	
	/**
	 * Returns the request method from _SERVER
	 *
	 * @return string $method
	 */
	function getMethod(): string;
	
	/**
	 * Returns the request body
	 *
	 * @return string $body
	 */
	function getBody(): string;

	/**
	 * Checks if the request method is GET
	 *
	 * @return bool
	 */
	function isGet(): bool;
	
	/**
	 * Checks if the request method is POST
	 *
	 * @return bool
	 */
	function isPost(): bool;
	
	/**
	 * Checks if the request method is PUT
	 *
	 * @return bool
	 */
	function isPut(): bool;

	/**
	 * Retrieves data from _GET
	 *
	 * @param string $key opt. key for specific value
	 * @return mixed
	 */
	function Get(string $key=null): mixed;
	
	/**
	 * Retrieves data from _POST
	 *
	 * @param string $key opt. key for specific value
	 * @return mixed
	 */
	function Post(string $key=null): mixed;
	
	/**
	 * Retrieves data from _PUT
	 *
	 * @param string $key opt. key for specific value
	 * @return mixed
	 */
	function Put(string $key=null): mixed;

	/**
	 * Sets a key in _GET
	 *
	 * @param string $key The variable key
	 * @param mixed $value The variable value
	 * @return void
	 */
	function setGet(string $key, mixed $value): void;
	
	/**
	 * Sets a key in _POST
	 *
	 * @param string $key The variable key
	 * @param mixed $value The variable value
	 * @return void
	 */
	function setPost(string $key, mixed $value): void;
	
	/**
	 * Sets a key in _PUT
	 *
	 * @param string $key The variable key
	 * @param mixed $value The variable value
	 * @return void
	 */
	function setPut(string $key, mixed $value): void;
	
	/**
	 * Sets a key in _REQUEST
	 *
	 * @param string $key The variable key
	 * @param mixed $value The variable value
	 * @return void
	 */
	function setRequest(string $key, mixed $value): void;

	/**
	 * Gets the current arguments
	 *
	 * @return array
	 */
	function getArguments(): array;
	
	/**
	 * Checks if an argument is provided
	 *
	 * @param string $key
	 * @return bool
	 */
	function hasArgument(string $key): bool;
	
	/**
	 * Gets an argument value by key
	 *
	 * @param string $key
	 * @return mixed
	 */
	function getArgument(string $key): mixed;
	
	/**
	 * Sets an argument value by key
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	function setArgument(string $key,mixed $value): void;

	/**
	 * Gets a file list from _FILES
	 *
	 * @param string $key The file list key
	 * @return array|null
	 */
	function getFiles(string $key): ?array;
	
	/**
	 * Checks if a file list exists in _FILES
	 *
	 * @param string $key The file list key
	 * @return bool
	 */
	function hasFiles(string $key): bool;
	
	/**
	 * Checks if a file exists in a file list
	 *
	 * @param string $key The file list key
	 * @param int $index The row index
	 * @return bool
	 */
	function hasFile(string $key,int $index): bool;
	
	/**
	 * Gets a file from a file list
	 *
	 * @param string $key The file list key
	 * @param string $subkey The information key. if empty, an associative array with all file information is returned
	 * @param int $index The row index
	 * @return mixed
	 */
	function getFile(string $key,string $subkey,int $index): mixed;  
}

?>