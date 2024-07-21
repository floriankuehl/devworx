<?php

namespace Devworx;

use \Devworx\Utility\StringUtility;
use \Devworx\Utility\ModelUtility;
use \Devworx\Utility\CookieUtility;

interface IController {
  function getId(): string;
  function getNamespace(): string;
  function getBlockRendering(): bool;
  function getRequest(): IRequest;
  function getView(): IView;
  
  function initialize();
  function processAction(string $action,...$arguments);
}

abstract class AbstractController implements IController {
  
  protected 
    $id = '',
    $namespace = '',
    $view = null,
    $request = null,
    $blockRendering = false;
  
  public function __construct(){
    $tokens = explode("\\",get_called_class());
    $this->id = str_replace("Controller","",array_pop($tokens));
    $this->namespace = implode("\\",$tokens);
    $this->view = new View($this->id);
    $this->request = new Request();
  }
  
  function getId(): string {
    return $this->id;
  }
  
  function getNamespace(): string {
    return $this->namespace;
  }
  
  function getRequest(): IRequest {
    return $this->request;
  }
  
  function getView(): IView {
    return $this->view;
  }
  
  function getBlockRendering(): bool {
    return $this->blockRendering;
  }
   
  abstract function initialize();
   
  function processAction(string $action,...$arguments){
    $path = Frontend::getConfig('view','templateRootPath');
    
    $controller = $this->getId();
    $action = ucfirst($action);
    $this->view->setId("{$controller}-{$action}");
    $this->view->setFile("{$path}/{$controller}/{$action}.php");
    $encoding = Frontend::getConfig('view','encoding');
    if( is_string($encoding) ) $this->view->setEncoding($encoding);
    $this->view->setProvideAll( Frontend::getConfig('doctype') === 'html' );
    $this->view->setVariables([]);
    $response = call_user_func([$this,"{$action}Action"],...$arguments);
    
    return $this->view->render($response);
  }
}

?>