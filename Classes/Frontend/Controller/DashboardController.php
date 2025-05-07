<?php

namespace Frontend\Controller;

use \Devworx\Frontend;
//use \Devworx\Utility\FlashMessageUtility;

class DashboardController extends \Devworx\AbstractController {

    public function initialize(){
		
	}
    
    public function indexAction(){
        $user = Frontend::getCurrentUser();
        $this->view->assign('user',$user);
    }
    
    public function profileAction(){
        $user = Frontend::getCurrentUser();
        $this->view->assign('user',$user);
    }
  
}
