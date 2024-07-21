<?php

namespace Frontend\Controller;

use \Devworx\Frontend;
//use \Devworx\Utility\FlashMessageUtility;

class DashboardController extends \Devworx\AbstractController {
    
  public function indexAction(){
    $user = Frontend::getCurrentUser();
    $this->view->assign('user',$user);
  }
  
  public function profileAction(){
    $user = Frontend::getCurrentUser();
    
    $errors = [];
    
    if( $this->request->isPost() ){
      $input = $this->request->getArgument('user');
      $removeImage = $this->request->hasArgument('removeProfileImage') && ( $this->request->getArgument('removeProfileImage') == 1 );
      
      $fileName = $user['login'];
      $profileImage = "./Resources/Public/Profiles/{$fileName}.jpg";
      
      if( array_key_exists('profileImage',$_FILES) ){
        $uploaded = FileUtility::upload('profileImage',$profileImage);
        $image = ImageUtility::resize($profileImage,300,300);
        $removeImage = false;
      }
      
      if( $removeImage && file_exists($profileImage) ){
        unlink($profileImage);
      }
      
      if( $this->request->hasArgument('actions') ){
        $actions = $this->request->getArgument('actions');
        if( is_string($actions) ) $actions = json_decode($actions,true);
        $image = ImageUtility::modify($profileImage,$actions);
      }
      
      $pw = ArrayUtility::key($input,'password',false);
      $pw2 = ArrayUtility::key($input,'password2',false);
      $name = ArrayUtility::key($input,'name',false);
      
      ArrayUtility::remove($input,'uid','name','login','admin','created','updated','deleted','password','password2');
      $input['uid'] = $user['uid'];
      $input['name'] = $user['name'];
      
      $pwChange = ( is_string($pw) && is_string($pw2) ) &&
        ValidationUtility::validate('password',$pw) &&
        ( $pw === $pw2 );
      
      $nameChange = $name != $user['name'];
      $pwConfirm = is_string($pw) && is_bool($pw2);
      $userEdit = $pwConfirm && ( $user['login'] == AuthUtility::getHash( $user['name'], $pw ) );

      if( $nameChange ){
        if( $userEdit ){
          if( ValidationUtility::validate('username',$name) ){
            $check = $this->userRepository->findOneBy('name',$name,'uid');
            if( is_null($check) || empty($check) ){
              $input['name'] = $name;
              $input['login'] = AuthUtility::getHash($name,$pw);
            } else
              $errors []= 'name.taken';
          } else
            $errors []= 'name.format';
        } else
          $errors []= 'name.confirmation';
      }
      
      foreach( $input as $k => $v ){
        if( array_key_exists($k,$user) )
          $user[$k] = $v;
      }
      $this->userRepository->put($input);
      $this->view->assign('errors',$errors);
    }
    
    //$user = ModelUtility::toModel($user,User::class);
    $this->view->assign('user',$user);
  }
}

?>
