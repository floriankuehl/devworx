<?php
namespace Api\Controller;

use \Api\AbstractController;
use \Devworx\View;
use \Devworx\Frontend;
use \Devworx\Utility\DebugUtility;

class ProviderController extends AbstractController {
  
  public function initialize(){
    $this->view->setEncoding('');
    $view = &Frontend::$config['view'];
    $view['layout'] = 'Resource';
    $view['renderer'] = 'Devworx\\Renderer\\FluidRenderer';
    $view['layoutRootPath'] = 'Resources/Frontend/Layouts';
    $view['templateRootPath'] = 'Resources/Frontend/Templates';
    $view['partialRootPath'] = 'Resources/Frontend/Partials';
    
    //Frontend::$config['view'] = $view;
    
  }
  
  public function partialAction(){
    $content = '';
    if( $this->request->isPost() ){
      
      $body = $this->request->getBody();
      $body = empty($body) ? [] : json_decode($body,true);
      
      if( !empty($body) )
        $content = View::Partial($body['name'],$body['variables']);
    }
    $this->view->assign('content',$content);
  }
}