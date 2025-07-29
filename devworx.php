<?php
	namespace Devworx;
	
	require_once 'Context/Devworx/Classes/Enums/KeyName.php';
	use \Devworx\Enums\KeyName;	
	
	// Globale Framework-Konfiguration
	$GLOBALS[ KeyName::Global->value ] = [
		KeyName::Framework->value => 'Devworx',	//name of the main framework and also the context folder 
		KeyName::Context->value => 'Devworx', 	//the current context
		KeyName::Contexts->value => [],			//all the contexts
		
		//parameters for the pdo connection
		KeyName::Database->value => [
			"127.0.0.1",
			"root",
			"",
			"storytime"
		],

		KeyName::Debug->value => true,		//debug flag

		//global keys
		KeyName::Key->value => [
			KeyName::ContextHeader->value => 'X-Devworx-Context',
			KeyName::ContextServer->value => 'REDIRECT_CONTEXT',
			KeyName::ContextLogin->value => 'X-Devworx-Api'
		],
		
		//folders / namespace names
		KeyName::Folder->value => [
			KeyName::Cache->value => 'Cache',
			KeyName::Context->value => 'Context',
				KeyName::Configuration->value => 'Configuration',
				KeyName::Classes->value => 'Classes',
					KeyName::Controller->value => 'Controller',
					KeyName::Repository->value => 'Repository',
					KeyName::Model->value => 'Model',
				KeyName::Resource->value => 'Resources',
					KeyName::Private->value => 'Private',
						KeyName::Layout->value => 'Layouts',
						KeyName::Template->value => 'Templates',
						KeyName::Partial->value => 'Partials',
					KeyName::Public->value => 'Public',
						KeyName::Script->value => 'Scripts',
						KeyName::Style->value => 'Styles',
						KeyName::Image->value => 'Images',
						KeyName::Font->value => 'Fonts',
		],
		
		//path information
		KeyName::Path->value => [
			KeyName::Private->value => '..', //public to root
			KeyName::Public->value => '.', //public to public
		]
	];
	
	//file_put_contents('test.json',json_encode($GLOBALS[ KeyName::Global->value ],JSON_PRETTY_PRINT));
	
	require_once 'Context/Devworx/Classes/Devworx.php';	
	Devworx::initialize();