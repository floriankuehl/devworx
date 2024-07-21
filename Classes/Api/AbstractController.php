<?php

namespace Api;

use \Api\Utility\ApiUtility;

class AbstractController extends \Devworx\AbstractController {
  
  public function initialize(){
    $this->view->setEncoding('json');
  }
  
  public function getProjectDetails(): ?string {
    $what = null;
    if( $this->request->hasArgument('project') ){
      switch($this->request->getArgument('project')){
        case'full':{ $what = '*'; }break;
        case'system':{ $what = \Devworx\Repository::getSystemFields(true); }break;
        case'short':{ $what = 'uid,name,startDate,endDate,customer'; }break;
      }
    }
    return $what;
  }
  
  public function getCustomerDetails(): ?string {
    $what = null;
    if( $this->request->hasArgument('customer') ){
      switch($this->request->getArgument('customer')){
        case'full':{ $what = '*'; }break;
        case'system':{ $what = \Devworx\Repository::getSystemFields(true); }break;
        case'short':{ $what = 'uid,company,salutation,title,firstName,lastName'; }break;
        case'address':{ $what = 'uid,company,address,address2,zip,city,country'; }break;
        case'contact':{ $what = 'uid,company,salutation,title,firstName,lastName,tel,email,www'; }break;
        case'company':{ $what = 'uid,company,companyUid,vat,tel,email,www,address,address2,zip,city,country'; }break;
      }
    }
    return $what;
  }
    
  public function getProtocolDetails(): ?string {
    $what = null;
    if( $this->request->hasArgument('protocol') ){
      switch($this->request->getArgument('protocol')){
        case'full':{ $what = "*"; } break;
        case'system':{ $what = \Devworx\Repository::getSystemFields(true); }break;
        case'short':{ $what = "uid,cruser,project,startDate,endDate,article,header,bodytext"; }break;
      }
    }
    return $what;
  }
  
  public function getArticleDetails(): ?string {
    $what = null;
    if( $this->request->hasArgument('article') ){
      switch($this->request->getArgument('article')){
        case'full':{ $what = "*"; } break;
        case'system':{ $what = \Devworx\Repository::getSystemFields(true); }break;
        case'short':{ $what = "uid,name,description,salary,fix"; }break;
      }
    }
    return $what;
  }
  
  public function getUserDetails(): ?string {
    $what = null;
    if( $this->request->hasArgument('user') ){
      switch($this->request->getArgument('user')){
        case'full':{ $what = "*"; } break;
        case'system':{ $what = \Devworx\Repository::getSystemFields(true); }break;
        case'short':{ $what = "uid,firstName,lastName"; }break;
      }
    }
    return $what;
  }
  
  public function getInvoiceDetails(): ?string {
    $what = null;
    if( $this->request->hasArgument('invoice') ){
      switch($this->request->getArgument('invoice')){
        case'full':{ $what = "*"; } break;
        case'system':{ $what = \Devworx\Repository::getSystemFields(true); }break;
        case'short':{ $what = "uid,invoice,project"; }break;
      }
    }
    return $what;
  }
  
  public function getDocumentDetails(): ?string {
    return $this->request->hasArgument('document') ? $this->request->getArgument('document') : null;
  }
  
}