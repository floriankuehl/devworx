<?php

namespace Devworx\Utility;

class OPCacheUtility {
  
  public static function build(){
    $directory = new \RecursiveDirectoryIterator(__DIR__ . '/Classes');
    $fullTree = new \RecursiveIteratorIterator($directory);
    $phpFiles = new \RegexIterator($fullTree, '/.+((?<!Test)+\.php$)/i', \RecursiveRegexIterator::GET_MATCH);
    foreach ($phpFiles as $key => $file) {
        \opcache_compile_file($file[0]);
    }
  }
  
  public static function flush(){
    opcache_reset();
    self::build();
  }
  
  
}

?>