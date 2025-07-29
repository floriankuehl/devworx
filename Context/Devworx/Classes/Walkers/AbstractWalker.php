<?php

namespace Devworx\Walkers;

use \Devworx\Interfaces\IWalker;

/**
 * Represents an abstract class for extending a list step by step
 */
abstract class AbstractWalker implements IWalker {

	/**
	 * The start hook of the walking cycle
	 * 
	 * @param array $list The list to extend
	 * @return void
	 */
	abstract function Start(array &$list): void;
	
	/**
	 * The step hook of the walking cycle
	 * 
	 * @param array $list The list to extend
	 * @param mixed $index The index of the current row
	 * @param mixed $row The current row of the list
	 * @return void
	 */
	abstract function Step(array &$list,$index,&$row): void;

	/**
	 * The walking cycle to perform the Start hook, the Step hook on each row of the list and the End hook afterwards
	 * 
	 * @param array $list The list to extend
	 * @return void
	 */
	public function Walk(array &$list): void {
		$this->Start($list);
		foreach($list as $i=>$row)
			$this->Step($list,$i,$row);
		$this->End($list);
	}

	/**
	 * The end hook of the walking cycle
	 * 
	 * @param array $list The list to extend
	 * @return void
	 */
	abstract function End(array &$list): void;
}
