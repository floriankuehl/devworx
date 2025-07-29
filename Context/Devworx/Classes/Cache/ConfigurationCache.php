<?php

namespace Devworx\Cache;

use \Devworx\Devworx;
use \Devworx\Cache\AbstractCache;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\FileUtility;
use \Devworx\Utility\ArrayUtility;

class ConfigurationCache extends AbstractFileCache {
	
	const SOURCE_FILE = 'Context.json';
	
	public function __construct(string $id,string $context){
		parent::__construct($id,$context,true);
	}
	
	function file(string $context,...$more):string {
		$context = strtolower( $context === '' ? Devworx::context() : $context );
		return "{$this->folder}/{$context}.json";
	}
	
	function create(string $context,...$more): bool {
		$file = $this->file($context);
		if( file_exists( $file ) )
			return true;

		$framework = Devworx::framework();
		$config = PathUtility::configuration($framework,self::SOURCE_FILE);
		
		if( empty($context) || ( $context === $framework ) )
			return FileUtility::set($file,FileUtility::get($config));
		
		$data = file_exists($config) ? FileUtility::getJson($config) : null;
		if( $data === null ){
			trigger_error("No configuration file found in {$config}",E_USER_WARNING);
			return false;
		}
		$addition = PathUtility::configuration($context,self::SOURCE_FILE);
		
		if( file_exists($addition) ){
			$contextData = FileUtility::getJson($addition);
			if( empty($data) ) 
				return $this->set($context,$contextData);
			return $this->set($context,ArrayUtility::merge($data,$contextData));
		}
		return $config;
	}
	
	
	
}