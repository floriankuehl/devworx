<?php

namespace Devworx\Cache;

use \Devworx\Context;
use \Devworx\Cache\AbstractCache;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\FileUtility;
use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\DebugUtility;

class CachesCache extends AbstractFileCache {
	
	function __construct(string $id,string $context){
		parent::__construct($id,$context,true);
	}
	
	function file(string $context,...$more):string {
		$context = strtolower( $context === '' ? Context::get() : $context );
		return "{$this->folder}/{$context}.json";
	}
	
	function create(string $context,...$more): bool {
		$file = $this->file($context,...$more);
		if( file_exists( $file ) ) return true;

		$config = PathUtility::configuration(Context::framework(),$this->id().'.json');
		
		if( empty($context) || ( $context === Context::framework() ) )
			return FileUtility::set($file,FileUtility::get($config));
		
		$data = file_exists($config) ? FileUtility::getJson($config) : [];
		$addition = PathUtility::configuration($context,$this->id().'.json');
		if( file_exists($addition) ){
			$contextData = FileUtility::getJson($addition);
			return empty($data) ? 
				$this->set($context,$contextData) : 
				$this->set($context,ArrayUtility::merge($data,$contextData));
		}
		return $this->set($context,$data);
	}
	
	function all(string $context): \Traversable {
		if( empty($context) ) $context = Context::framework();
		return new \ArrayIterator( $this->get($context) );
	}
	
}