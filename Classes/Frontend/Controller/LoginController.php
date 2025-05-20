<?php

namespace Frontend\Controller;

use \Devworx\Frontend;
use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\AuthUtility;
use \Devworx\Utility\FlashMessageUtility;

class LoginController extends \Devworx\AbstractController {
  
  function initialize(): void {
    
  }
  
  function registerAction(){
    global $DB;
    
    if( $this->request->isPost() ){
      
      $user = $this->request->getArgument('user');
      ArrayUtility::remove($user,'uid','created','updated','deleted');
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
  
  function loginAction(){
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
  
  function logoutAction(){
    AuthUtility::lock();
    Frontend::redirectDefault();
  }
}
