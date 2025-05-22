<?php

namespace Documentation\Controller;

use \Devworx\Utility\DebugUtility;
use \Documentation\Utility\DoxygenUtility;

class DocumentationController extends \Devworx\AbstractController {
	
	public function initialize(): void {
		$this->setBlockLayout(true);
	}
	
	public function generateAction(){
		$result = DoxygenUtility::Doxygen();
		$result = DebugUtility::var_dump( $result );
		$this->view->assign('content',$result);
	}
	
	public function showAction(){
		$file = $this->request->getArgument('file') ?? 'index.html';
		if( empty($file) ) $file = 'index.html';

		$content = DoxygenUtility::Route($file);
		$this->view->assign('content',$content);
	}
	
}
