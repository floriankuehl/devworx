<?php

namespace Devworx\Controller;

use \Devworx\Frontend;
use \Devworx\Redirect;
use \Devworx\Configuration;
use \Devworx\Utility\AuthUtility;
use \Devworx\Utility\FlashMessageUtility;
use \Devworx\Utility\ModelUtility;
use \Devworx\Utility\ArrayUtility;

use \Devworx\Repository\UserRepository;
use \Devworx\Model\User;

class UserController extends \Devworx\Controller\AbstractController {
    
	protected $user = false;
	protected $userRepository = null;
	
	public function initialize(): void {
		$this->user = Frontend::getCurrentUser() ?? false;
		$this->userRepository = new UserRepository();
	}
	
	public function loginAction(){
				
		if( AuthUtility::cookie() || AuthUtility::post() ){
			//Referrer Tracking?
			$ca = explode('::',Configuration::get('system','afterLogin'));
			switch( count($ca) ){
				case 1: Redirect::to($ca[0]); break;
				case 2: Redirect::action(...$ca); break;
			}
			return;
		}

		if( $this->request->isPost() ){
			FlashMessageUtility::Add('warning','Credentials not found');
			return;
		}

		AuthUtility::lock();	
	}

	public function logoutAction(){
		if( $this->user ){
			AuthUtility::lock();
			Redirect::default();
		}
	}

	public function registerAction(){
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

				$user['uid'] = $this->userRepository->add($user);
				$this->view->assign('user',$user);
			}
		}
	}

	public function indexAction(){
		if( $this->user ){
			$this->view->assign('user',$this->user);
		}
	}
  
	public function profileAction(){
		if( $this->user ){
			$user = ModelUtility::toModel( $this->user, User::class );
			$this->view->assign('user',$user);
		}
	}
  
	public function updateAction(){
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
		  $user['uid'] = $currentUser['uid'];
		  $this->userRepository->put($user);
		}
		
		Redirect::action('User','index');
		
	}
}
