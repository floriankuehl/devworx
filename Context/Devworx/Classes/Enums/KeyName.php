<?php

namespace Devworx\Enums;

enum KeyName: string {
	case Global = 'DEVWORX';
	
	case Key = 'KEY';
	case ContextHeader = 'CONTEXT_HEADER';
	case ContextServer = 'CONTEXT_SERVER';
	case ContextLogin = 'CONTEXT_LOGIN';
		
	case Debug = 'DEBUG';
	
	case Database = 'DB';
	case Framework = 'FRAMEWORK';
	case Context = 'CONTEXT';
	case Contexts = 'CONTEXTS';
	
	case Folder = 'FOLDER';
	case Cache = 'CACHE';
	
	case Classes = 'CLASSES';
		case Controller = 'CONTROLLER';
		case Model = 'MODEL';
		case Repository = 'REPOSITORY';
	case Configuration = 'CONFIGURATION';
	case Resource = 'RESOURCE';
		//public
		case Script = 'SCRIPT';
		case Style = 'STYLE';
		case Image = 'IMAGE';
		case Font = 'FONT';
		//private
		case Template = 'TEMPLATE';
		case Layout = 'LAYOUT';
		case Partial = 'PARTIAL';
	
	case Path = 'PATH';
		case Private = 'PRIVATE';
		case Public = 'PUBLIC';
}

?>