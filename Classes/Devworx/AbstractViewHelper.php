<?php

namespace Devworx;

abstract class AbstractViewHelper {
   
  public $arguments = null;
  public $values = null;
   
  abstract function initializeArguments();
  abstract function render();
  
  function hasArgument(string $key)
  
  function process($values){
    $this->initializeArguments();
    
    foreach( $this->arguments as $name => $arg ){
      $this->values[$name] = ( 
        array_key_exists($name,$values) && 
        gettype($values[$name]) == $arg[0]
      ) ? $values[$name] : $arg[2];
    }
    
    return $this->render();
  }
}

?>