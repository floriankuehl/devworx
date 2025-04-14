<?php

namespace Devworx\Walkers;

interface IWalker {
  function Start(array &$list): void;
  function Step(array &$list,$index,&$row): void;
  function Walk(array &$list): void;
  function End(array &$list): void;
}

abstract class AbstractWalker implements IWalker {
  abstract function Start(array &$list): void;
  abstract function Step(array &$list,$index,&$row): void;
  
  public function Walk(array &$list): void {
    $this->Start($list);
    foreach($list as $i=>$row)
      $this->Step($list,$i,$row);
    $this->End($list);
  }
  
  abstract function End(array &$list): void;
}