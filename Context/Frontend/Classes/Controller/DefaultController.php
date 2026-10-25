<?php

namespace Frontend\Controller;

class DefaultController extends \Devworx\Controller\AbstractController {
  
	public function initialize(): void {
		
	}
  
	public function indexAction(){
		$this->view->assign('foo','bar');
	}
 
}

?>
