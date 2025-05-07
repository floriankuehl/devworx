<?php

namespace Frontend\Controller;

use \Devworx\Frontend;
use \Devworx\Utility\FlashMessageUtility;
use \Devworx\Utility\ModelUtility;
use \Frontend\Models\User;

class DashboardController extends \Devworx\AbstractController {
    
	
	public function initialize(){
		
	}
	
	public function indexAction(){
		$user = Frontend::getCurrentUser();
		$this->view->assign('user',$user);
	}

	public function profileAction(){
		$user = ModelUtility::toModel( Frontend::getCurrentUser(), User::class );
		$this->view->assign('user',$user);
	}
  
}
