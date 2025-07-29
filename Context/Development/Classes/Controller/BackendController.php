<?php

namespace Development\Controller;

use Devworx\Utility\FileUtility;
use Devworx\Utility\DebugUtility;
use Devworx\Frontend;

class BackendController extends \Devworx\Controller\AbstractController {
	
	public function initialize(): void {
		
	}
	
	public function indexAction(){
		
	}
	
	function cascadeAction(){
		$this->view->assign('user',Frontend::getCurrentUser() ?? false);
	}
	
	function getWmic(string $cmd): ?array {
		$result = null;
		if (stristr(PHP_OS, "win")){
            @exec("wmic {$cmd} get /all /format:list", $output);
            if ($output){
				$result = [];
				
				$row = [];
                foreach ($output as $i => $line){
					$line = trim($line);
					if( empty($line) ){ 
						if( !empty($row) ){
							$result []= $row;
							$row = [];
						}
						continue;
					}
					$line = explode('=',$line);
					$row[$line[0]] = $line[1];
                }
            }
        }
		return $result;
	}
	
	function getCpu(): ?array {
		return $this->getWmic('cpu');
	}
	
	function getMemoryChips(): ?array {
		return $this->getWmic("memorychip");
	}
	
	function getPhysicalMemory(): ?array {
		return $this->getWmic("memphysical");
	}
	
	function getGpu(): ?array {
		return $this->getWmic("path win32_VideoController");
	}
	
	function analyseFiles(string $name,string $folder,array $exclude=[]): bool {
		$list = \Devworx\Utility\FileUtility::analyseFolder($folder,$exclude,false);
		if( $list === null ) return false;
		
		return FileUtility::setJson( 
			"./logs/{$name}.json", 
			$list, 
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES 
		);
	}
	
	public function analyseContext(string $context,array $without=[]){
		
		$folder = '../Context/'.$context;
		$this->analyseFiles($context.'.classes',$folder.'/Classes',$without);
		$this->analyseFiles($context.'.config',$folder.'/Configuration',$without);
		$this->analyseFiles($context.'.private',$folder . '/Resources/Private',$without);
		$this->analyseFiles($context.'.public',$folder . '/Resources/Public',$without);
	}
	
	public function analyseAction(){
		$this->analyseContext('Devworx');
		$this->analyseContext('Development');
		$this->analyseContext('Frontend');
		$this->analyseContext('Api');
		$this->analyseContext('Documentation',[
			'../Context/Documentation/Doxygen/Documentation'
		]);
		
		$this->analyseFiles('Cache','../Cache');
		$this->analyseFiles('Devworx.cascade','../Context/Devworx/Classes/Cascade');
		$this->analyseFiles('All','..',[
			'../Cache',
			'../Context/Documentation/Doxygen/Documentation',
			'../Public/resources',
			'../Public/logs',
			'../Context/Devworx/Resources/Public/Images',
			'../Context/Development/Resources/Public/Images',
			'../Context/Documentation/Resources/Public/Images',
			'../Context/Documentation/Resources/Public/Images',
		]);
		
		FileUtility::setJson(
			'logs/server.json',
			[
				'mem' => $this->getPhysicalMemory(),
				'ram' => $this->getMemoryChips(),
				'cpu' => $this->getCpu(),
				'gpu' => $this->getGpu()
			]
		);
		
		$this->redirect('files');
	}
	
	public function filesAction(){
		
		$statics = [
			'logs/performance.json',
			'logs/server.json',
		];
		
		$file = $this->request->getArgument('file') ?? 'All.json';
		$available = FileUtility::findFiles('logs',false,$statics);
		
		$this->view->assign('currentFile',$file);
		$this->view->assign('index',$available);
		
		$files = FileUtility::getJson("logs/{$file}") ?? [];
		$performance = FileUtility::getJson($statics[0]) ?? ['data'=>[]];
		$server = FileUtility::getJson($statics[1]) ?? ['cpu'=>[],'gpu'=>[],'mem'=>[],'ram'=>[]];
		
		$this->view->assign('files',$files);
		$this->view->assign('performance',$performance);
		$this->view->assign('server',$server);
	}
	
}