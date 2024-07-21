<?php

namespace Frontend\ViewHelpers;

class RenderViewHelper extends \Devworx\AbstractViewHelper {
  
  public function initializeArguments(){
    
    $this->arguments = [
      'section' => ['string','Section Name',''],
      'partial' => ['string','Partial Name',''],
      'controller' => ['string','Controller Name',''],
      'action' => ['string','Action Name',''],
    ];
    
  }
  
  public function render(){
    
    
    
  }
  
  
}