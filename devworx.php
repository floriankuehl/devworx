<?php
  namespace Devworx;

  $GLOBALS['DEVWORX'] = [
    'CONTEXT' => [
      'frontend' => 'Frontend Context',
      'api' => 'API Context',
      'documentation' => 'Documentation Context',
      'development' => 'Development Context'
    ],
    'DB' => [
      "localhost",
      "root",
      "",
      "devworx"
    ],
    'CFG' => [
      
    ],
    'PATH' => [
      'ROOT' => '..',
      'PUBLIC' => '.',
    ]
  ];

  spl_autoload_register(function ($class) {
    $file = $GLOBALS['DEVWORX']['PATH']['ROOT'] . "/Classes/" . str_replace('\\', '/', $class) . ".php";
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
  });
  
  //Utility::OPCacheUtility::build();
  
  set_exception_handler([Utility\DebugUtility::class,'exception']);
  
  $DB = new Database(...$GLOBALS['DEVWORX']['DB']);
  if( !$DB->connected() ) $DB->connect();
