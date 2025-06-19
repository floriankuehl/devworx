<?php

namespace Devworx\Cache;

class PerformanceCache extends AbstractVariableCache {
	
	function create(string $context,...$more): bool {
		return $this->set($context,$more);
	}
	
	function time(): float {
		return microtime(true);
	}
	
	function nextIndex(string $context): int {
		return $this->has($context) ? count($this->entries[$context]) : 0;
	}
	
	function currentIndex(string $context): int {
		return $this->nextIndex($context)-1;
	}
	
	function current(string $context): array {
		return $this->has($context) ? 
			$this->entries[$context][ $this->currentIndex($context) ] : 
			null;
	}
	
	function start(string $context): bool{
		if( $this->has($context) ){
			$current = $this->current($context);
			
			if( count($current) === 1 ){
				throw new \Exception("current entry is not finished");
				return false;
			}
			
			$this->entries[$context][]= [$this->time()];
			return true;
		}
		$this->entries[$context][] = [$this->time()];
		return true;
	}
	
	function stop(string $context): bool{
		if( $this->has($context) ){
			$current = $this->current($context);
			if( count($current) === 2 ){
				throw new \Exception("current entry is already finished");
				return false;
			}
			
			$this->entries[$context][ $this->currentIndex($context) ][] = $this->time();
			return true;
		}
		return false;
	}
	
	
}