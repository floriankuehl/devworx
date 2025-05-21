<?php

namespace Frontend\Controller;

use \Devworx\Frontend;
use \Devworx\Utility\FileUtility;

class DocumentationController extends \Devworx\AbstractController {
	
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
	
	public function initialize(): void {
		$this->setBlockLayout(true);
		$this->view->setRenderer('');
	}
	
	protected function prepareDoxyfile(): string {
		
		$doxygen = Frontend::getConfig('doxygen');
		$constants = realpath( Frontend::path($doxygen['constants']) );
		$output = dirname( $constants ) . DIRECTORY_SEPARATOR . $doxygen['doxyfile'];
		
		$project = $doxygen['project'];
		$docset = $doxygen['docset'];
		$html = $doxygen['html'];
		
		$path = implode(DIRECTORY_SEPARATOR,[
			dirname( $constants ),
			$doxygen['output'],
			$html['output']
		]); 
		
		$addition = [];
		foreach([
			'DOXYFILE_ENCODING' => strtoupper(Frontend::getConfig('charset')),
			'OUTPUT_DIRECTORY' => $doxygen['output'],
			'EXCLUDE' => $doxygen['exclude'],
			'OUTPUT_LANGUAGE' => $doxygen['language'],
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
		] as $k => $v){
			$addition []= "{$k} = {$v}";
		}
		
		$content = file_exists($constants) ? file_get_contents($constants) : '';
		$addition = implode(PHP_EOL,$addition);
		
		if( file_exists($output) )
			unlink($output);
		
		FileUtility::unlinkRecursive( $path );
		
		file_put_contents($output, "{$content}\r\n{$addition}");
		
		return $output;
	}
	
	public function generateAction(){
		$doxygen = Frontend::getConfig('doxygen','bin');
				
		$info = [
			'message' => 'doxygen not installed',
			'doxygen' => false,
			'doxyfile' => false,
			'workdir' => false,
			'command' => false,
			'output' => false,
		];
		
		if( file_exists($doxygen) ){
			$info['doxygen'] = $doxygen;
			$configFile = $this->prepareDoxyfile();
			$info['message'] = 'doxyfile not found';
			if( file_exists($configFile) ){
				$info['doxyfile'] = $configFile;
				$info['workdir'] = dirname($configFile);
				$info['command'] = "\"{$doxygen}\" \"{$configFile}\" 2>&1";
				
				chdir($info['workdir']);
				exec($info['command'],$output,$returnCode);
				chdir('Public');
				
				$info['message'] = empty($returnCode) ? 'Success' : 'Error';
				$info['output'] = implode(PHP_EOL,$output);		
			}
		}
		
		$result = \Devworx\Utility\DebugUtility::var_dump($info);
		
		$this->view->assign('content',$result);
	}
	
	public function routeAction(){
				
		// Basisverzeichnis außerhalb von DocumentRoot
		$baseDir = Frontend::path( 
			Frontend::getConfig('doxygen','output'), 
			Frontend::getConfig('doxygen','html','output') 
		);
		
		// Dateiname aus GET-Parameter
		$file = $this->request->getArgument('file') ?? 'index.html';
		if( empty($file) ) $file = 'index.html';

		// Sicherheit: Pfad bereinigen, um Directory Traversal zu verhindern
		$cleanPath = realpath("{$baseDir}/{$file}");
	
		$content = null;
		
		if ($cleanPath && file_exists($cleanPath)) {
			$info = strtolower( pathinfo($file,PATHINFO_EXTENSION) );
			$mime = self::MIME[$info];
			header("Content-Type: $mime");
			
			$content = file_get_contents($cleanPath);
			if (preg_match('/\.html?$/i', $cleanPath)) {
				$dir = dirname($file);
				$fileName = basename($file);
				$baseHref = '/help/' . ($dir !== '.' ? $dir . '/' : '') . $fileName;
				$content = preg_replace('/<head([^>]*)>/i', "<head$1>\n<base href=\"{$baseHref}\">", $content, 1);
			}
		}
		
		$this->view->assign('content',$content);
	}
	
}