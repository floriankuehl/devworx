<?php
	namespace Devworx;

	$GLOBALS['DEVWORX'] = [
		'CONTEXT' => [
			'api' => 'API context',
			'frontend' => 'Frontend context',
			'documentation' => 'Documentation context',
			'development' => 'Development context'
		],
		'DB' => [
			"localhost",
			"root",
			"",
			"storytime"
		],
		'CFG' => [
			'debug' => true
		],
		'PATH' => [
			'ROOT' => '..',
			'PUBLIC' => '.',
		]
	];

	ini_set('display_errors', $GLOBALS['DEVWORX']['CFG']['debug']); 
	ini_set('display_startup_errors', $GLOBALS['DEVWORX']['CFG']['debug']); 
	error_reporting($GLOBALS['DEVWORX']['CFG']['debug'] ? E_ALL : 0);
	
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
