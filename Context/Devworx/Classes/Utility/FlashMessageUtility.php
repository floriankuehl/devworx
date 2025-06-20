<?php

namespace Devworx\Utility;

use Devworx\View;
  
class FlashMessageUtility {
  
  const TYPES = [
    'info' => [],
    'warning' => [],
    'error' => []
  ];
  
  /**
   * Adds a flashMessage to the session
   *
   * @param string $type The flashMessage type
   * @param string $message The flashMessage text
   * @return void
   */
  static function Add(string $type, string $message): void {
    $_SESSION['flashMessages'][$type][]= [
      'type' => $type,
      'message' => $message,
    ];
  }
  
  /**
   * Flushes all flashMessages from the session
   *
   * @return void
   */
  static function Flush(){
    $_SESSION['flashMessages'] = self::TYPES;
  }
  
  /**
   * Renders a single flashMessage by partial
   *
   * @param string $type The flashMessage type
   * @param string $message The flashMessage text
   * @return string
   */
  static function Single(string $type, string $message): string {
    return View::Partial("FlashMessages/Show",[
      'type' => $type,
      'message' => $message
    ])->render();
  }
  
  /**
   * Renders a flashMessage list by partial
   *
   * @param string $type The flashMessage type
   * @param string $messages The flashMessage texts
   * @return string
   */
  static function List(string $type, array $messages): string {
    return View::Partial("FlashMessages/List",[
      'type' => $type,
      'messages' => $messages
    ])->render();
  }
  
  /**
   * Renders all flashMessages inside the session
   *
   * @return string
   */
  static function handle(): string {
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
