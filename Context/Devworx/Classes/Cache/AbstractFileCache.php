<?php

namespace Devworx\Cache;

use \Devworx\Interfaces\IFileCache;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\FileUtility;
use \Devworx\Context;

abstract class AbstractFileCache extends AbstractCache implements IFileCache {
	
	protected $json = false;
	protected $folder = '';
	protected $list = [];
	
	function __construct(string $id,string $context,bool $json=false){
		parent::__construct($id, $context);
		$this->folder = PathUtility::cache( $id );
		if( !is_dir($this->folder) ) mkdir($this->folder,0777,true);
		$this->json = $json;
	}
	
	/**
	 * Returns an instance of this cache
	 * 
	 * @param array $arguments the variadic argument list for the constructor
	 * @return IFileCache
	 */
	static function Instance(...$args): IFileCache {
		$instance = parent::Instance(...$args);
		if (!($instance instanceof IFileCache)) {
			throw new \RuntimeException("returned instance is not of type IFileCache");
		}
		return $instance;
	}
	
	function isJson(): bool {
		return $this->json;
	}
	
	function setJson(bool $value=true): void {
		$this->json = $value;
	}
	
	function getFolder(): string {
		return $this->folder;
	}
	
	function setFolder(string $value): void {
		$this->folder = trim($folder,"\\/");
	}
	
	function getList(): array {
		return $this->list;
	}
	
	function setList(array $value): void {
		$this->list = $list;
	}
	
	abstract function file(string $context,...$more):string;
	
	function get(string $context,...$more): mixed {
		$file = $this->file($context,...$more);
		return $this->json ? 
			FileUtility::getJson($file) : 
			FileUtility::get($file);
	}
	
	function set(string $context,mixed $value,...$more): bool {
		$file = $this->file($context,...$more);
		return $this->json ? 
			FileUtility::setJson($file,$value) : 
			FileUtility::set($file,$value);
	}
	
	function has(string $context,...$more): bool {
		return file_exists( $this->file($context,...$more) );
	}
	
	function all(string $context): \Traversable {
		$content = $this->get($context,...$more);
		if( !is_array($content) ) 
			$content = [$content];
		return new \ArrayIterator($content);
	}
	
	function flush(string $context,...$more): bool {
		return unlink( $this->file($context,...$more) );
	}
	
	function needsUpdate(string $context,...$more): bool {
		$result = false;
		
		if( empty($context) ){
			foreach( $this->contexts() as $ctx ){
				if( $result ) break;
				if( $ctx === '' ) continue;
				
				if( empty($this->list) ){
					$result = !file_exists( $this->file($ctx,...$more) );
					continue;
				}
				
				$list = false;
				foreach( $this->list as $item ){
					$list = !file_exists( $this->file($ctx,$item,...$more) );
					if( $list ) break;
				}
				$result = $result || $list;
			}
			return $result;
		}
		
		if( empty( $this->list ) )
			return !file_exists( $this->file($context,...$more) );
		
		foreach( $this->list as $item ){
			$result = !file_exists( $this->file($context,$item,...$more) );
			if( $result ) break;
		}
		
		return $result;
	}
	
	/**
	 * Initializes the cache
	 *
	 * @param string $context optional arbitrary key
	 * @param array $more more arguments for inheriting classes
	 * @return bool $result cache is ready
	 */
	function initialize(string $context,...$more): bool {
						
		if( empty($this->list) ){
			return parent::initialize($context,...$more);
		}
		
		$result = true;
		$contexts = empty($context) ? Context::contexts() : [$context];
		
		foreach( $contexts as $ctx ){
			foreach( $this->list as $item ){
				if( $this->needsUpdate($ctx,$item,...$more) )
					$result = $result && $this->create($ctx,$item,...$more);
			}
		}			
		return $result;
	}
}