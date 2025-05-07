<?php

namespace Frontend\Controller;

use \Devworx\Frontend;
use \Devworx\Utility\FlashMessageUtility;
use \Devworx\Utility\ModelUtility;
use \Devworx\Utility\ArrayUtility;
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
  
  
	public function updateProfileAction(){
		global $DB;
    
		if( $this->request->isPost() ){
		  
		  $user = $this->request->getArgument('user');
		  
		  $currentUser = Frontend::getCurrentUser();
		  ArrayUtility::remove($user,'uid','name','login','created','updated','deleted');
		  
		  $valid = !ArrayUtility::empty($user,[
			'firstName',
			'lastName',
			'address',
			'address2',
			'zip',
			'city',
			'country',
			'email',
			'tel'
		  ]);
		  
		  if( 
			array_key_exists('name',$user) && 
			array_key_exists('password',$user) && 
			array_key_exists('password2',$user) 
		  ){
			  if( $user['password'] === $user['password2'] ){
				$user['login'] = AuthUtility::createUserHash($user['name'],$user['password']);
				unset($user['password']);
				unset($user['password2']);
				$this->view->assign('user',$user);
			  }
		  }
		  $DB->put('user','uid',$currentUser['uid'],$user);
		}
		
		Frontend::redirect('Dashboard','index');
		
	}
}
