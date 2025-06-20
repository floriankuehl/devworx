<?php

namespace Devworx\Walkers;

/**
 * Represents an abstract class for extending a list by data from a subset
 */
abstract class AbstractSubsetWalker extends AbstractWalker {
 
 
	/** @var array $subset The subset retrieved by getSubset */
	public $subset = null;
	/** @var array $arguments The arguments passed to this by the constructor */
	public $arguments = null;
    
	/** 
	 * Constructor
	 *
	 * @param array $arguments The arguments stored for internal usage 
	 */
	public function __construct(...$arguments){
		$this->arguments = $arguments;
		$this->subset = [];
	}
  
	/** 
	 * The function to retrieve a subset, based on the given $list array
	 *
	 * @param array $list The data list
	 * @return array
	 */
	abstract function getSubset(array &$list): array;
  
	/** 
	 * The start hook for the Walker, that executes at the start of the cycle
	 *
	 * @param array $list The data list
	 * @return void
	 */
	public function Start(array &$list): void {
		$this->subset = $this->getSubset($list);
	}
  
	/** 
	 * The end hook for the Walker, that executes at the end of the cycle.
	 * Clears the subset from the class.
	 *
	 * @param array $list The data list
	 * @return void
	 */
	public function End(array &$list): void {
		unset($this->subset);
	}
}


?>
