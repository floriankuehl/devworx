<?php

namespace Api\Walkers;

use \Devworx\Utility\ArrayUtility;

class CruserExtender extends \Devworx\Walkers\AbstractSubsetWalker {
  
  public function __construct(...$arguments){
    parent::__construct(...$arguments);
  }
  
  public function getSubset(array &$list): array {
    $repository = new \Devworx\Repository('user','');
    $uids = array_unique( array_column( $list, 'cruser' ) );
    $result = $repository->filter([
      'uid' => $uids,
    ],...$this->arguments);
    /*
    echo \Devworx\Utility\DebugUtility::var_dump([
      'uids' => $uids,
      'arguments' => $this->arguments,
      'result' => $result,
      'index' => ArrayUtility::index($result,'uid')
    ],__CLASS__,__METHOD__,__LINE__);
    */
    $result []= \Devworx\Frontend::SYSTEM_USER;
    
    return ArrayUtility::index($result,'uid');
  }
  
  public function Step(array &$list,$index,&$row): void {
    $row['cruserUid'] = $row['cruser'];
    $row['cruser'] = $this->subset[ $row['cruser'] ];
    $list[$index] = $row;
  }
}


?>