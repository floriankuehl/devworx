<?php

namespace Devworx\Interfaces;

/**
 * Interface for basic caches
 */
interface ICache {

	/**
	 * Returns an instance of this cache
	 * 
	 * @param array $args the variadic arguments for the constructor
	 * @return ICache
	 */
	static function Instance(...$args): ICache;

	/**
	 * Returns the id of the cache
	 * 
	 * @return string
	 */
	function id(): string;
	
	/**
	 * Checks if the cache instance is global
	 *
	 * @return bool $result
	 */
	function global(): bool;
	
	/**
	 * Gets the value of the cache or if context is provided, the context related value
	 *
	 * @param string $context an optional arbitrary context
	 * @param array $more more arguments for inheriting classes
	 * @return mixed $result
	 */
	function get(string $context,...$more): mixed;
	
	/**
	 * Sets an entry in the cache
	 *
	 * @param string $context an arbitrary context
	 * @param mixed $value an arbitrary value
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result operation successfull
	 */
	function set(string $context,mixed $value,...$more): bool;
	
	/**
	 * Returns a Traversable for the cached content
	 *
	 * @param string $context an arbitrary context
	 * @return Traversable $result the iterator
	 */
	function all(string $context): \Traversable;
	
	/**
	 * Flushes this cache or if provided, a specific context related value
	 *
	 * @param string $context optional arbitrary key
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result operation successfull
	 */
	function flush(string $context,...$more): bool;
			
	/**
	 * Populates this cache or if provided, a specific context related value
	 *
	 * @param string $context optional arbitrary key
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result operation successfull
	 */
	function create(string $context,...$more): bool;
	
	/**
	 * Checks if the cache needs an update
	 *
	 * @param string $context optional arbitrary key
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result cache needs recreation
	 */
	function needsUpdate(string $context,...$more): bool;
	
	/**
	 * Initializes the cache
	 *
	 * @param string $context optional arbitrary key
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result cache is ready
	 */
	function initialize(string $context,...$more): bool;
}

?>