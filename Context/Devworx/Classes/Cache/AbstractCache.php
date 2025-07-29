<?php

namespace Devworx\Cache;

use \Devworx\Interfaces\ICache;
use \Devworx\Devworx;


abstract class AbstractCache implements ICache {
	
	private $cacheID = null;
	private $cacheContext = null;
	
	public function __construct(string $id,string $context=''){
		$this->cacheID = $id;
		$this->cacheContext = $context;
	}
	
	/**
	 * Returns an instance of this cache
	 * 
	 * @param array $args the arguments for the constructor
	 * @return ICache
	 */
	static function Instance(...$args): ICache {
		if( empty($args) ) $args = [Context::get()];
		$className = get_called_class();
		return new $className(...$args);
	}
	
	/**
	 * Returns the id of the cache
	 * 
	 * @return string
	 */
	function id(): string {
		return $this->cacheID;
	}
	
	/**
	 * Returns the context name of the cache
	 *
	 * @return string
	 */
	function context(): string {
		return $this->cacheContext;
	}
		
	/**
	 * Checks if the cache is global
	 *
	 * @return bool $result cache has no context
	 */
	function global(): bool {
		return empty($this->cacheContext);
	}
	
	/**
	 * Checks if a cache entry exists
	 *
	 * @param string $context optional arbitrary context
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result cache entry exists
	 */
	abstract function has(string $context,...$more): bool;
	
	/**
	 * Gets the value of the cache or if context is provided, the context related value
	 *
	 * @param string $context an optional arbitrary context
	 * @param array $more arguments for inheriting classes
	 * @return mixed $result
	 */
	abstract function get(string $context,...$more): mixed;
	
	/**
	 * Sets an entry in the cache
	 *
	 * @param string $context an arbitrary context
	 * @param mixed $value an arbitrary value
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result operation successfull
	 */
	abstract function set(string $context,mixed $value,...$more): bool;
	
	/**
	 * Returns a Traversable for the cached content
	 *
	 * @param string $context an arbitrary context
	 * @return Traversable $result the iterator
	 */
	abstract function all(string $context): \Traversable;
	
	/**
	 * Flushes this cache or if provided, a specific context related value
	 *
	 * @param string $context optional arbitrary key
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result operation successfull
	 */
	abstract function flush(string $context,...$more): bool;
			
	/**
	 * Populates this cache or if provided, a specific context related value
	 *
	 * @param string $context optional arbitrary context
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result operation successfull
	 */
	abstract function create(string $context,...$more): bool;
	
	/**
	 * Checks if the cache needs an update
	 *
	 * @param string $context optional arbitrary context
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result cache needs recreation
	 */
	abstract function needsUpdate(string $context,...$more): bool;
	
	/**
	 * Initializes the cache
	 *
	 * @param string $context optional arbitrary key
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result cache is ready
	 */
	function initialize(string $context,...$more): bool {
		if( empty($context) ){
			$result = true;
			foreach( Devworx::contexts() as $ctx ){
				if( $this->needsUpdate($ctx,...$more) )
					$result = $result && $this->create($ctx,...$more);
			}
			return $result;	
		}
		
		return $this->needsUpdate($context,...$more) && $this->create($context,...$more);
	}
}