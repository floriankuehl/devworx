<?php

namespace Devworx\Utility;

use Devworx\View;
  
class FlashMessageUtility {
  
  const TYPES = [
    'info' => [],
    'warning' => [],
    'error' => []
  ];
  
  static function Add(string $type, string $message){
    $_SESSION['flashMessages'][$type][]= [
      'type' => $type,
      'message' => $message,
    ];
  }
  
  static function Flush(){
    $_SESSION['flashMessages'] = self::TYPES;
  }
  
  static function Single(string $type, string $message){
    return View::Partial("FlashMessages/Show",[
      'type' => $type,
      'message' => $message
    ])->render();
  }
  
  static function List(string $type, array $messages){
    return View::Partial("FlashMessages/List",[
      'type' => $type,
      'messages' => $messages
    ])->render();
  }
  
  static function handle(){
    if( array_key_exists('flashMessages',$_SESSION) ){
      $messages = $_SESSION['flashMessages'];
      if( is_array($messages) ){
        ob_start();
        foreach(self::TYPES as $type=>$std){
          if( 
            array_key_exists($type,$messages) && 
            is_array($messages[$type]) 
          ){
            echo self::List($type,$messages[$type]);
          }
        }
        self::Flush();
        return ob_get_clean();
      }
    }
    return '';
  }
  
}
  

?>