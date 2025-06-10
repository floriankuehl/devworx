<?php

namespace Frontend\Controller;

use \Devworx\Frontend;
use \Devworx\Utility\AuthUtility;
use \Devworx\Utility\FlashMessageUtility;
use \Devworx\Utility\ModelUtility;
use \Devworx\Utility\ArrayUtility;
use \Frontend\Models\User;

class UserController extends \Devworx\AbstractController {
    
	protected $userID = 0;
	protected $user = [];
	
	public function initialize(): void {
		$this->user = Frontend::getCurrentUser();
		$this->userID = $this->user['uid'];
	}
	
	public function indexAction(){
		$this->view->assign('user',$this->user);
	}
 
	public function registerAction(){
		global $DB;

		if( $this->request->isPost() ){

			$user = $this->request->getArgument('user');
			ArrayUtility::remove($user,'uid','created','updated','deleted','hidden','cruser');
			
			//TODO: RegEx Validations?
			$valid = !ArrayUtility::empty($user,[
				'name',
				'password',
				'password2',
				//'gender',
				'firstName',
				'lastName',
				'address',
				'address2',
				'zip',
				'city',
				'country',
				'email',
				//'tel'
			]);

			if( $user['password'] === $user['password2'] ){
				$user['login'] = md5($user['name'] . "|" . $user['password']);

				unset($user['password']);
				unset($user['password2']);

				$user['uid'] = $DB->add('user',$user);
				$this->view->assign('user',$user);
			}
		}
	}

	public function loginAction(){
		if( AuthUtility::cookie() || AuthUtility::post() ){
			//Referrer Tracking?
			$ca = explode('::',Frontend::getConfig('system','afterLogin'));
			Frontend::redirect(...$ca);
			return;
		}

		if( $this->request->isPost() ){
			FlashMessageUtility::Add('warning','Credentials not found');
			return;
		}

		AuthUtility::lock();
	}

	public function logoutAction(){
		AuthUtility::lock();
		Frontend::redirectDefault();
	}
  
	public function profileAction(){
		if( empty($this->user) ) return;
		$user = ModelUtility::toModel( $this->user, User::class );
		$this->view->assign('user',$user);
	}
  
	public function updateAction(){
		global $DB;
    
		if( $this->request->isPost() ){
		  
		  $user = $this->request->getArgument('user');
		  
		  $currentUser = $this->user;
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
		
		Frontend::redirect('User','index');
		
	}
}
