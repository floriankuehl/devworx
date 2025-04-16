<?php

namespace Api;

use \Api\Utility\ApiUtility;

class AbstractController extends \Devworx\AbstractController {
  
  public function initialize(){
    $this->view->setEncoding('json');
  }
  
}
