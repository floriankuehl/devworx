<?php

namespace Api;

use \Api\Utility\ApiUtility;

class AbstractController extends \Devworx\AbstractController {
  
  /**
   * Function initialize
   * Initializes an API Controller with JSON encoding
   * 
   * @return void
   */
  public function initialize(): void {
    $this->view->setEncoding('json');
  }
  
}
