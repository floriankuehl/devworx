<?php
	// Globale Framework-Konfiguration
	$GLOBALS['DEVWORX'] = [
	
		//name of the main framework and also the context folder 
		'FRAMEWORK' => 'Devworx',
		
		'CONTEXT' => 'Devworx',
		'CONTEXTS' => [],
		
		//parameters for the connect function
		'DB' => [
			"127.0.0.1",
			"root",
			"",
			"devworx"
		],

		//global configurations
		'CFG' => [
			'DEBUG' => true,
			'CONTEXT_HEADER' => 'X-Devworx-Context',
			'CONTEXT_SERVER' => 'REDIRECT_CONTEXT',
			'CONTEXT_LOGIN' => 'X-Devworx-Api'
		],
		
		//path information
		'PATH' => [
			'CONTEXT' => 'Context',
			'CACHE' => 'Cache',
			'CONFIG' => 'Configuration',
			'ROOT' => '..',
			'PUBLIC' => '.',
		]
	];
	
	// Error Handling
	if ($GLOBALS['DEVWORX']['CFG']['DEBUG']) {
		ini_set('display_errors', '1');
		ini_set('display_startup_errors', '1');
		error_reporting(E_ALL);
	} else {
		ini_set('display_errors', '0');
		ini_set('display_startup_errors', '0');
		error_reporting(0);
	}
	
	require_once 'Context/Devworx/Classes/Frontend.php';
	\Devworx\Frontend::initialize();
