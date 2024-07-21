<?php

namespace Frontend\Controller;

class CacheController extends \Devworx\AbstractController {
  
  const CACHES = [
    'Repository',
    'Models',
    'OPCache'
  ];
  const DIRECTORIES = ['.','..'];
  
  public function initialize(){
        
  }

  public function findAllFiles(string $path,bool $filesOnly=false): ?array {
    $files = null;
    if( is_dir($path) ){
      $files = array_filter(scandir($path),fn($row) => !in_array($row,self::DIRECTORIES));
      $files = array_map(fn($row) => "{$path}/{$row}",$files);
      if( $filesOnly )
        return array_filter($files,fn($row) => !is_dir($row));
    }
    return $files;
  }
  
  public function unlinkFiles(string $path): ?array {
    $files = $this->findAllFiles($path);
    if( is_null($files) ) return $files;
    $files = array_map(fn($row) => "{$path}/{$row}",scandir($path));
    return array_filter($files,fn($row) => is_file($row) && unlink($row));
  }
  
  public function flushCache(string $cache){
    $cache = ucfirst($cache);
    switch( $cache ){
      case'Models':{ 
        $this->unlinkFiles('Classes/Frontend/' . $cache); 
        \Devworx\Utility\BuildUtility::checkModels();
      }break;
      /*case'OPCache':{
        \Devworx\Utility\OPCacheUtility::flush();
      }break;*/
      default:{ 
        $this->unlinkFiles('Cache/' . $cache); 
        $this->rebuildCache($cache);
      } break;
    }
  }
  
  public function rebuildCache(string $cache){
    $cache = ucfirst($cache);
    switch($cache){
      case'Models':{}break;
      case'Billomat':{ 
        \Api\Utility\BillomatUtility::GETAll('clients');
        \Api\Utility\BillomatUtility::GETAll('incomings');
        \Api\Utility\BillomatUtility::GETAll('incoming-items');
        \Api\Utility\BillomatUtility::GETAll('invoices');
        \Api\Utility\BillomatUtility::GETAll('invoice-items');
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
