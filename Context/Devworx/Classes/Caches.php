<?php

namespace Devworx;

use \Devworx\Interfaces\ICache;
use \Devworx\Interfaces\IFileCache;

use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\PathUtility;
use \Devworx\Cache\CachesCache;

use \Devworx\Utility\DebugUtility;

class Caches {
	
	/** @var array<string,ICache> $list A list of registered caches */
	private static $list = [];
	
	/**
	 * Iterator for all caches (use forEach(self::items() as $id => $cache))
	 * 
	 * @return Traversable
	 */
	public static function items(): \Traversable {
        return new ArrayIterator(self::$list);
    }
	
	/**
	 * Returns all cache ids
	 * 
	 * @return array $result array keys of the list
	 */
	public static function ids(): array {
		return array_keys( self::$list );
	}
	
	/**
	 * Checks if an id is present in list
	 * 
	 * @param string $id the id to check
	 * @return bool
	 */
	public static function has(string $id): bool {
		return array_key_exists($id,self::$list);
	}
	
	/**
	 * Gets a cache by id or returns a cache entry
	 * 
	 * @param string $id the id to read from
	 * @param string $key optional subkey for ICache::get, if null, the cache is returned
	 * @param array $more optional arguments for the get() subfunction
	 * @return mixed $result possible cache instance or cache entry
	 */
	public static function get(string $id,string $key=null,...$more): mixed {
		return isset( self::$list[$id] ) ? (
			$key === null ? 
				self::$list[$id] : 
				self::$list[$id]->get($key,...$more)
			) : null;
	}
	
	/**
	 * Sets a cache to a specific key
	 * 
	 * @param string $id the id to write to
	 * @param ICache $cache the cache to add
	 * @param string $key (opt.) key of the cache to write to
	 * @param mixed $value (opt.) value of the writing operation
	 * @param array $more optional arguments for the get() subfunction
	 * @return bool
	 */
	public static function set(string $id,ICache $cache,string $key=null,mixed $value=null,...$more): bool {
		if( empty($id) ) $id = $cache->id();
		if( empty($id) ) return false;
		self::$list[$id] = $cache;
		return ( $key === null ) || self::$list[$id]->set($key,$value,...$more);
	}
	
	/**
	 * Adds a cache to the list
	 * 
	 * @param ICache $cache the cache to add
	 * @return bool
	 */
	public static function add(ICache $cache): bool {
		if( empty($cache->id()) ) 
			return false;
		return self::set( $cache->id(), $cache );
	}
	
	
	/**
	 * Loads multiple caches to the caching framework
	 * 
	 * @param string $className the fqcn of the cache
	 * @param string $id the id of the cache
	 * @param string $context the context for the function
	 * @param array $more arguments passed to the cache initialization
	 * @return bool $result successfully added
	 */
	public static function load(string $className,string $id,string $context,...$more): bool {
		if( class_exists( $className ) ){
			$cache = $className::Instance($id,$context,...$more);
			$cache->initialize($context,...$more);
			return self::add($cache);
		}
		throw new \Exception("Classname {$className} not found in Caches::load");
		return false;
	}
	
	/**
	 * Loads multiple caches to the caching framework
	 * 
	 * @param array $caches the list of caches
	 * @param string $context the context for the function
	 * @return array $result list of bools
	 */
	public static function loadAll(array $caches,string $context): bool {
		$result = [];
		foreach( $caches as $id => $className ){
			$result[$id] = self::load($className,$id,$context);
		}
		return $result;
	}
	
	/**
	 * Removes a cache by id
	 * 
	 * @param string $id the id to write to
	 * @return ICache $result the removed cache
	 */
	public static function remove(string $id): ?ICache {
		if( self::has($id) ){
			$cache = self::get($id);
			unlink(self::$list[$id]);
			return $cache;
		}
		return null;	
	}
	
	/**
	 * Triggers the create function of a cache by id
	 * 
	 * @param string $id the id to write to
	 * @param string $context the context for the function
	 * @param array $more the variable arguments for the function
	 * @return bool
	 */
	public static function create(string $id,string $context,...$more): bool {
		return self::has($id) ? 
			self::$list[$id]->create($context,...$more) :
			false;
	}
	
	/**
	 * Triggers the flush function of a cache by id
	 * 
	 * @param string $id the id to write to
	 * @param string $context the context for the function
	 * @param array $more the variable arguments for the function
	 * @return bool
	 */	
	public static function flush(string $id,string $context,...$more): bool {
		return self::has($id) ? 
			self::$list[$id]->flush($context,...$more) :
			false;
	}
	
	/**
	 * Triggers the needUpdate function of a cache by id
	 * 
	 * @param string $id the id to write to
	 * @param string $context the context for the function
	 * @param array $more the variable arguments for the function
	 * @return bool
	 */
	public static function needsUpdate(string $id,string $context,...$more): bool {
		return self::has($id) ? 
			self::$list[$id]->needsUpdate($context,...$more) :
			false;
	}
	
	/**
	 * Initializes the caching framework
	 * 
	 * @param string $context the context for the function
	 * @return bool $result initialized
	 */
	public static function initialize(string $context=''): bool {
		$result = self::load('Devworx\Cache\CachesCache','Caches',$context);
		if( $result ){
			foreach( self::get('Caches')->all($context) as $id => $className ){
				$result = $result && self::load($className,$id,$context);
			}
			return $result;
		}
		
		throw new \Exception("CachesCache could not be loaded");
		
		return $result;
	}
}