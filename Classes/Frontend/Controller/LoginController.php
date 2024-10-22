<?php

namespace Frontend\Controller;

use \Devworx\Frontend;
use \Devworx\Utility\ArrayUtility;
use \Devworx\Utility\AuthUtility;
use \Devworx\Utility\FlashMessageUtility;

class LoginController extends \Devworx\AbstractController {

  const START_CONTROLLER = 'dashboard';
  const START_ACTION = 'index';
  
  function initialize(){
    
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
        $user['login'] = hash('md5', $user['name'] . "|" . $user['password'], true);
        //TODO: global hashing method, integration in AuthUtility
        //$user['login'] = hash('sha256', $user['name'] . "|" . $user['password'], true);
        
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
      Frontend::redirect(self::START_CONTROLLER,self::START_ACTION);
      return;
    }
    
    if( $this->request->isPost() ){
      FlashMessageUtility::Add('warning','Credentials not found');
      return;
    }
    
    AuthUtility::lock();
    /*
    echo \Devworx\Utility\DebugUtility::var_dump([
      'cookie' => AuthUtility::cookie(),
      'post' => AuthUtility::post(),
    ]);
    */
  }
  
  function logoutAction(){
    AuthUtility::lock();
    Frontend::redirect('login','login');
  }
}
