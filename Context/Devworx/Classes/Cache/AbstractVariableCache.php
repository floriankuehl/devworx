<?php

namespace Devworx\Cache;

class AbstractVariableCache extends AbstractCache {
	
	protected $entries = [];
	
	public function __construct(string $id,string $context){
		parent::__construct($id,$context,true);
	}
	
	function has(string $context,...$more): bool {
		if( empty($context) ) 
			return false;
		return array_key_exists($context,$this->entries);
	}
	
	function set(string $context,mixed $value,...$more): bool {
		if( empty($context) ) 
			return false;
		$this->entries[$context] = $value;
		return true;
	}
	
	function get(string $context,...$more): mixed {
		if( empty($context) || !array_key_exists($context,$this->entries) )
			return null;
		return $this->entries[$context];
	}
	
	function all(string $context=''): \Traversable {
		return new \ArrayIterator($this->entries);
	}
	
	function needsUpdate(string $context,...$more): bool {
		return !array_key_exists($context,$this->entries);
	}
	
	function create(string $context,...$more): bool {
		if( empty($more) )
			return $this->set($context,$more);
		
		if( count($more) === 1 && isset($more[0]) )
			return $this->set($context,array_shift($more));
		
		return $this->set($context,$more);
	}
	
	function flush(string $context,...$more): bool {
		if( empty($context) ){
			$this->entries = [];
			return true;
		}
		if( $this->has($context) ){
			unset($this->entries[$context]);
			return true;
		}
		return false;
	}	
}


?>