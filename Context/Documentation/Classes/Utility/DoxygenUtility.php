<?php

namespace Documentation\Utility;

use \Devworx\Frontend;
use \Devworx\Configuration;
use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\FileUtility;
use \Devworx\Utility\DebugUtility;

class DoxygenUtility {
	
	const MIME = [
		'js' => 'text/javascript',
		'html' => 'text/html',
		'htm' => 'text/html',
		'css' => 'text/css',
		'png' => 'image/png',
		'jpg' => 'image/jpg',
		'xml' => 'application/xml',
		'' => 'text/plain',
	];
	
	public static function Config(): array {
		return Configuration::get('doxygen');
	}
	
	public static function Constants(): string {
		$config = self::Config();
		$constants = Frontend::contextPath( $config['constants'] );
		return file_exists($constants) ? file_get_contents($constants) : '';
	}
	
	public static function Addition(): string {
		$config = self::Config();
		$warnings = Frontend::contextPath( $config['workdir'], $config['warnings'] );
		$output = $config['workdir'] . DIRECTORY_SEPARATOR .  $config['output'];
		
		$project = $config['project'];
		$docset = $config['docset'];
		$html = $config['html'];
		
		return ArrayUtility::joinAssoc([
			'DOXYFILE_ENCODING' => strtoupper(Frontend::getConfig('charset')),
			'WARN_LOGFILE' => $warnings,
			'OUTPUT_DIRECTORY' => $output,
			'EXCLUDE' => $config['exclude'],
			'OUTPUT_LANGUAGE' => $config['language'],
			'HTML_OUTPUT' => $html['output'],
			'PROJECT_NAME' => $project['name'],
			'PROJECT_NUMBER' => $project['version'],
			'PROJECT_BRIEF' => '"' . $project['description'] . '"',
			'PROJECT_LOGO' => $project['logo'],
			'PROJECT_ICON' => $project['icon'],
			'DOCSET_FEEDNAME' => '"' . $docset['name'] . '"',
			'DOCSET_FEEDURL' => $docset['url'],
			'DOCSET_BUNDLE_ID' => $docset['id'],
			'DOCSET_PUBLISHER_ID' => $docset['id'].".documentation",
			'DOCSET_PUBLISHER_NAME' => '"' . $docset['publisher'] . '"',
			'HTML_PROJECT_COOKIE' => Frontend::getConfig('cookie','name'),
			'HTML_HEADER' => $html['header'], 
			'HTML_FOOTER' => $html['footer'],
			'HTML_STYLESHEET' => $html['stylesheet'], 
			'HTML_EXTRA_STYLESHEET' => $html['extraStylesheet'],
			'HTML_EXTRA_FILES' => $html['files'],
			'HTML_COLORSTYLE' => $html['colorStyle'],
			'HTML_COLORSTYLE_HUE' => $html['hue'],
			'HTML_COLORSTYLE_SAT' => $html['saturation'],
			'HTML_COLORSTYLE_GAMMA' => $html['gamma']
		], ' = ',PHP_EOL);
	}
	
	public static function Doxyfile(): string {
		$config = self::Config();
		$doxyfile = PathUtility::currentContext( $config['workdir'], $config['doxyfile'] );
		
		$realOutput = PathUtility::currentContext( $config['workdir'], $config['output'] );
		$realOutput = realpath($realOutput);
		if( empty($realOutput) ) return '';
		//FileUtility::unlinkRecursive( $realOutput );
		
		$constants = self::Constants();
		$addition = self::Addition();
		
		if( file_exists($doxyfile) )
			unlink($doxyfile);
		file_put_contents($doxyfile, "{$constants}\r\n{$addition}");
		return realpath( $doxyfile );
	}
	
	public static function Doxygen(): array {
		$config = Configuration::get('doxygen');
		$binary = $config['bin'];
				
		$info = [
			'message' => 'doxygen not installed',
			'binary' => false,
			'doxyfile' => false,
			'workdir' => '..',
			'command' => false,
			'output' => false,
		];
		
		if( file_exists($binary) ){
			$info['binary'] = $binary;
			$doxyfile = self::Doxyfile();
			$info['message'] = 'doxyfile not found';
			if( file_exists($doxyfile) ){
				$info['doxyfile'] = $doxyfile;
				$info['command'] = "\"{$binary}\" \"{$doxyfile}\" 2>&1";
				
				$workdir = PathUtility::currentContext($info['workdir']);
				$back = PathUtility::relative( $workdir, getcwd() );
				
				chdir( $workdir );
				exec($info['command'],$output,$returnCode);
				chdir( $back );
				
				$info['message'] = empty($returnCode) ? 'Success' : 'Error';
				$info['output'] = implode(PHP_EOL,$output);		
			}
		}
		
		return $info;
	}
	
	public static function Route(string $fileName): string {
		
		$config = self::Config();

		$path = PathUtility::currentContext(
			$config['workdir'],
			$config['output'], 
			$config['html']['output']
		);
		
		$content = null;
		
		if( !is_dir($path) ){
			$content = DebugUtility::var_dump([
				'current' => getcwd(),
				'path' => $path,
			]);
		}
		
		$absolute = realpath( "{$path}\\{$fileName}" );
		if( !file_exists($absolute) ){
			$content = DebugUtility::var_dump([
				'current' => getcwd(),
				'path' => $path,
				'absolute' => $absolute
			]);
		}
		
		if( is_null($content) ){
			$info = strtolower( pathinfo($fileName,PATHINFO_EXTENSION) );
			$mime = self::MIME[$info];
			header("Content-Type: $mime");
			
			$content = file_get_contents($absolute);
			if (preg_match('/\.html?$/i', $fileName)) {
				$content = preg_replace('/<head([^>]*)>/i', "<head$1>\n<base href=\"/documentation/{$fileName}\">", $content, 1);
			}
		}
		return $content;
	}
}
