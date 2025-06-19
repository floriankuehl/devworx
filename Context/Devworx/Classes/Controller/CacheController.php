<?php

namespace Devworx\Controller;

use \Devworx\Frontend;
use \Devworx\Context;
use \Devworx\Caches;
use \Devworx\Redirect;
use \Devworx\Utility\FileUtility;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\BuildUtility;
use \Documentation\Utility\DoxygenUtility;

class CacheController extends AbstractController {
  
	protected $cacheFolder = '';
	protected $modelFolders = [];
  
	public function initialize(): void {
		$this->cacheFolder = PathUtility::cache();

		foreach( Context::contexts() as $context ){
			$folder = PathUtility::context($context,'Models');
			if( is_dir($folder) )
				$this->modelFolders[$context] = $folder;
		}
	}
  
	public function flushCache(string $cacheName,string $context=''){
		$cacheName = ucfirst($cacheName);
		switch( $cacheName ){
			case'Models':{
				/*
				foreach( $this->modelFolders as $context => $folder ){
					FileUtility::unlinkAll( $folder ); 
				}
				*/
			}break;
			case'Documentation': {
				$ctx = Context::get();
				if( Frontend::loadContext('Documentation') ){
					$config = ConfigManager::get('doxygen');
					$folder = realpath( PathUtility::currentContext( $config['workdir'], $config['output'] ) );
					if( empty($folder) ) return false;
					FileUtility::unlinkRecursive( $folder );
					Frontend::loadContext($ctx);
					return true;
				}
			} break;
			default:{
				if( empty($context) ){
					foreach( Context::contexts() as $context ){
						Caches::flush($cacheName,$context);
					}
					return true;
				}
				return Caches::flush($cacheName,$context);
			}break;
		}
	}
  
	public function rebuildCache(string $cacheName,string $context=''){
		$cacheName = ucfirst($cacheName);
		switch($cacheName){
			case'Models':{ 
				//BuildUtility::checkModels(); 
			}break;
			case'Documentation':{
				$context = Context::get();
				if( Frontend::loadContext('Documentation') ){
					DoxygenUtility::Doxygen();
					Frontend::loadContext($context);
				}
			}break;
			default: {
				if( empty($context) ){
					foreach( Context::contexts() as $ctx ){
						Caches::create($cacheName,$ctx);
					}
					return true;
				}
				return Caches::create($cacheName,$context);
			} break;
		}
	}
  
	public function flushAction(){
		$cache = $this->request->hasArgument('cache') ? 
			$this->request->getArgument('cache') : 
			'';

		if( $cache === '' ) return;

		$context = $this->request->getArgument('context') ?? '';
		if( $context === '' ) $context = Context::get();
		
		$rebuild = $this->request->getArgument('rebuild') ?? false;
		
		$this->flushCache($cache,$context);	
		if( $rebuild )
			$this->rebuildCache($cache,$context);
					
		Redirect::referrer();
	}
  
}


?>
