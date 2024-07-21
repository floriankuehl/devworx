<?php

namespace Devworx\Walkers;

abstract class AbstractSubsetWalker extends AbstractWalker {
  
  public $subset = null;
  public $arguments = null;
    
  public function __construct(...$arguments){
    $this->arguments = $arguments;
    $this->subset = [];
  }
  
  abstract function getSubset(array &$list): array;
  
  public function Start(array &$list): void {
    $this->subset = $this->getSubset($list);
  }
  
  public function End(array &$list): void {
    unset($this->subset);
  }
}


?>