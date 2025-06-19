<?php

namespace Devworx\Interfaces;

/**
 * Interface for basic file caches
 */
interface IFileCache extends ICache {

	/**
	 * Returns an instance of this cache
	 * 
	 * @param array $args the variadic arguments for the constructor
	 * @return IFileCache
	 */
	static function Instance(...$args): IFileCache;

	function isJson(): bool;
	function setJson(bool $value=true): void;

	function getFolder(): string;
	function setFolder(string $value): void;
	
	function getList(): array;
	function setList(array $value): void;

	/**
	 * returns a file path based on the cache folder, context and more
	 * 
	 * @param string $context the given context
	 * @param array $more variable arguments
	 * @return string $result the connected file path
	 */
	function file(string $context,...$more): string;
}

?>