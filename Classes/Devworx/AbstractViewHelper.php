<?php

namespace Devworx;

/**
 * The base class for viewhelpers (experimental)
 * unfinished!
 */
abstract class AbstractViewHelper {
   
	/** @var array The arguments passed to the viewhelper, based on the values array */
	public $arguments = null;
	/** @var array The values passed to the viewhelper */
	public $values = null;

	/**
	 * The function to initialize the viewhelper arguments
	 *
	 * @return void
	 */
	abstract function initializeArguments(): void;
	
	/**
	 * The render function
	 *
	 * @return mixed
	 */
	abstract function render();

	/**
	 * Checks if the viewhelper has a specific argument set
	 *
	 * @return bool
	 */
	function hasArgument(string $key): bool {
		return array_key_exists($key, $this->arguments);
	}

	/**
	 * Processes the viewhelper argument and value logic, finally renders
	 *
	 * @param array $values
	 * @return mixed
	 */
	function process(array $values){
		$this->initializeArguments();

		foreach( $this->arguments as $name => $arg ){
		  $this->values[$name] = ( 
			array_key_exists($name,$values) && 
			gettype($values[$name]) == $arg[0]
		  ) ? $values[$name] : $arg[2];
		}

		return $this->render();
	}
}

?>
