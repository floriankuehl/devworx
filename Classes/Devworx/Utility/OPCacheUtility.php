<?php

namespace Devworx\Utility;

class OPCacheUtility {
  
  /**
   * Compiles certain files for the opcache (experimental)
   *
   * @return void
   */
  public static function build(): void {
    $directory = new \RecursiveDirectoryIterator(__DIR__ . '/Classes');
    $fullTree = new \RecursiveIteratorIterator($directory);
    $phpFiles = new \RegexIterator($fullTree, '/.+((?<!Test)+\.php$)/i', \RecursiveRegexIterator::GET_MATCH);
    foreach ($phpFiles as $key => $file) {
        \opcache_compile_file($file[0]);
    }
  }
  
  /**
   * Flushes and rebuilds the opcache
   *
   * @return void
   */
  public static function flush(){
    opcache_reset();
    self::build();
  }
  
  
}

?>
