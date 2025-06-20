<?php

namespace Devworx\Cache;

use \Devworx\Context;
use \Devworx\Cache\AbstractCache;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\FileUtility;
use \Devworx\Utility\ArrayUtility;

class ConfigurationCache extends AbstractFileCache {
	
	public function __construct(string $id,string $context){
		parent::__construct($id,$context,true);
	}
	
	function file(string $context,...$more):string {
		$context = strtolower( $context === '' ? Context::get() : $context );
		return "{$this->folder}/{$context}.json";
	}
	
	function create(string $context,...$more): bool {
		$file = $this->file($context);
		if( file_exists( $file ) )
			return true;

		$config = PathUtility::configuration(Context::framework(),$this->id().'.json');
		
		if( empty($context) || ( $context === Context::framework() ) )
			return FileUtility::set($file,FileUtility::get($config));
		
		$data = file_exists($config) ? FileUtility::getJson($config) : [];
		$addition = PathUtility::configuration($context,$this->id().'.json');
		
		if( file_exists($addition) ){
			$contextData = FileUtility::getJson($addition);
			if( empty($data) ) return $this->set($context,$contextData);
			return $this->set($context,ArrayUtility::merge($data,$contextData));
		}
		return $config;
	}
	
	
	
}