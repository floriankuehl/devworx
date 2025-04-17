<?php

namespace Api\Walkers;

use \Devworx\Utility\ArrayUtility;

class CruserExtender extends \Devworx\Walkers\AbstractSubsetWalker {
  
  public function __construct(...$arguments){
    parent::__construct(...$arguments);
  }
  
  /**
   * Function getSubset
   *
   * Retrieves a given subset
   *
   * @param array $list The list that will be extended
   * @return array
   */
  public function getSubset(array &$list): array {
    $repository = new \Devworx\Repository('user','');
    $uids = array_unique( array_column( $list, 'cruser' ) );
    $result = $repository->filter([
      'uid' => $uids,
    ],...$this->arguments);
    
    $result []= \Devworx\Frontend::SYSTEM_USER;
    
    return ArrayUtility::index($result,'uid');
  }
  
  /**
   * Function Step
   *
   * Performs a step of the subset walker to extend the given row
   *
   * @param array $list The list that will be extended
   * @param mixed $index The current row index
   * @param mixed $row The current row
   * @return void
   */
  public function Step(array &$list,$index,&$row): void {
    $row['cruserUid'] = $row['cruser'];
    $row['cruser'] = $this->subset[ $row['cruser'] ];
    $list[$index] = $row;
  }
}


?>
