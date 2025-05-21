<?php

namespace Documentation\Controller;

use \Devworx\Frontend;
use \Devworx\Utility\FileUtility;
use \Devworx\Utility\ArrayUtility;

class DocumentationController extends \Devworx\AbstractController {
	
	const SOURCE_PATH = 'Configuration';
	
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
	
	private function path(string $fileName){
		return realpath( Frontend::path($fileName) );
	}
	
	protected function getDoxygenConfig(){
		return Frontend::getConfig('doxygen');
	}
	
	protected function prepareDoxyfile(): string {
		
		$doxygen = $this->getDoxygenConfig();
		
		$constants = Frontend::realPath( $doxygen['workdir'], $doxygen['constants'] );
		$warnings = Frontend::realPath( $doxygen['workdir'], $doxygen['warnings'] );
		$doxyfile = Frontend::realPath( $doxygen['workdir'], $doxygen['doxyfile'] );
		
		$project = $doxygen['project'];
		$docset = $doxygen['docset'];
		$html = $doxygen['html'];
		
		$constants = file_exists($constants) ? file_get_contents($constants) : '';
		$output = Frontend::realPath( $doxygen['workdir'], $doxygen['output'] ); 		
		
		$addition = ArrayUtility::joinAssoc([
			'DOXYFILE_ENCODING' => strtoupper(Frontend::getConfig('charset')),
			'WARN_LOGFILE' => $warnings,
			'OUTPUT_DIRECTORY' => $output,
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
		], ' = ',PHP_EOL);
		
		if( file_exists($doxyfile) )
			unlink($doxyfile);
		
		FileUtility::unlinkRecursive( $output );
		
		file_put_contents($doxyfile, "{$constants}\r\n{$addition}");
		
		return $doxyfile;
	}
	
	public function generateAction(){
		$doxygen = Frontend::getConfig('doxygen');
		$binary = $doxygen['bin'];
				
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
			$doxyfile = $this->prepareDoxyfile();
			$info['message'] = 'doxyfile not found';
			if( file_exists($doxyfile) ){
				$info['doxyfile'] = $doxyfile;
				$info['command'] = "\"{$binary}\" \"{$doxyfile}\" 2>&1";
				
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
	
	public function showAction(){

		// Dateiname aus GET-Parameter
		$file = $this->request->getArgument('file') ?? 'index.html';
		if( empty($file) ) $file = 'index.html';

		$doxygen = Frontend::getConfig('doxygen');

		// Basisverzeichnis außerhalb von DocumentRoot
		$path = Frontend::path( 
			$doxygen['workdir'],
			$doxygen['output'], 
			$doxygen['html']['output']
		);
		
		if( !is_dir($path) ){
			$content = getcwd() . ' misses ' . $path;
			return;
		}
		
		$absolute = realpath( "{$path}\\{$file}" );
		if( !file_exists($absolute) ){
			$content = getcwd() . ' cant find ' . $absolute;
			return;
		}
		
		$content = null;	
		$info = strtolower( pathinfo($file,PATHINFO_EXTENSION) );
		$mime = self::MIME[$info];
		header("Content-Type: $mime");
		
		$content = file_get_contents($absolute);
		if (preg_match('/\.html?$/i', $file)) {
			$fileName = basename($file);
			$baseHref = '/help/' . $file;
			$content = preg_replace('/<head([^>]*)>/i', "<head$1>\n<base href=\"{$baseHref}\">", $content, 1);
		}
		
		$this->view->assign('content',$content);
	}
	
}