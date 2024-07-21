<?php
  namespace Devworx;

  spl_autoload_register(function ($class) {
    $file = "Classes/" . str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
  });
  
  //Utility::OPCacheUtility::build();
  
  set_exception_handler([Utility\DebugUtility::class,'exception']);
  
  $GLOBALS['DEVWORX'] = [
    'DB' => [
      "localhost",
      "root",
      "",
      "devworx"
    ],
    'CFG' => []
  ];
  
  $DB = new Database(...$GLOBALS['DEVWORX']['DB']);
  if( !$DB->connected() ) $DB->connect();