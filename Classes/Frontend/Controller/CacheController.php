<?php

namespace Frontend\Controller;

use \Devworx\Frontend;
use \Devworx\Utility\FileUtility;
use \Devworx\Utility\BuildUtility;
use \Documentation\Utility\DoxygenUtility;

class CacheController extends \Devworx\AbstractController {
  
  const CACHES = [
    'Repository',
    'Models',
    'OPCache',
	'Documentation'
  ];
  
  private $cacheFolder = '';
  private $modelFolder = '';
  
  public function initialize(): void {
    $this->cacheFolder = Frontend::getConfig('system','cache');
    $this->modelFolder = Frontend::path('Classes','Frontend','Models');
  }
  
  public function flushCache(string $cache){
    $cache = ucfirst($cache);
    switch( $cache ){
      case'Models':{ 
        FileUtility::unlinkAll( $this->modelFolder ); 
      }break;
      /*case'OPCache':{
        \Devworx\Utility\OPCacheUtility::flush();
      }break;*/
	  case'Documentation': {
		if( Frontend::loadContext('documentation') ){
			$config = Frontend::getConfig('doxygen');
			$folder = Frontend::realPath( $config['workdir'], $config['output'] );
			FileUtility::unlinkRecursive( $folder );
			Frontend::loadContext('frontend');
		}
	  } break;
      default:{ 
        FileUtility::unlinkAll( Frontend::path( $this->cacheFolder, $cache ) ); 
      } break;
    }
    $this->rebuildCache($cache);
  }
  
  public function rebuildCache(string $cache){
    $cache = ucfirst($cache);
    switch($cache){
      case'Models':{
        BuildUtility::checkModels();
      }break;
	  case'Documentation':{
		if( Frontend::loadContext('documentation') ){
			DoxygenUtility::Doxygen();
			Frontend::loadContext('frontend');
		}
	  }break;
    }
  }
  
  public function flushAction(){
    $cache = $this->request->hasArgument('cache') ? 
      $this->request->getArgument('cache') : 
      'all';
    
    if( $cache == 'all' ){
      foreach( self::CACHES as $i=>$cache ){
        $this->flushCache($cache);
      }
    } else 
      $this->flushCache($cache);
    header("Location: " . $_SERVER['HTTP_REFERER']);
  }
  
}


?>
