<?php

namespace Cascade\ViewHelper;

use Cascade\Runtime\Context;
use Cascade\Interfaces\INode;

/**
 * The base class for viewhelpers (experimental)
 * unfinished!
 */
abstract class AbstractViewHelper {
	
	/** @var array The arguments available in this viewhelper */
	protected $arguments = [];
	
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
	abstract function render(Context|array $context,array $arguments=null,mixed $input=null): mixed;
	
	
	public function registerArgument(string $name,string $type,mixed $default=null,bool $mandatory=false,bool $main=false){
		$name = lcfirst($name);
		
		$this->arguments[$name] = [
			'name' => $name,
			'type' => $type,
			'default' => $default,
			'mandatory' => $mandatory,
			'main' => $main,
		];
	}
	
	public function hasArgument(string $name): bool {
		return array_key_exists($name,$this->arguments);
	}
	
	public function matchesArgument(string $name,string $type): bool {
		if( $this->hasArgument($name) ){
			$types = explode('|',$this->arguments[$name]['type']);
			return in_array($type,$types,true);
		}
		return array_key_exists($name,$this->arguments);
	}
	
	public function getMainArgument(): ?array {
		$args = array_filter($this->arguments,fn($a)=>$a['main']);
		return array_shift($args);
	}
	
	public function getMandatoryArguments(): array {
		return array_filter($this->arguments,fn($a)=>$a['mandatory']);
	}
	
	public function mergeArguments(array $arguments=null,mixed $input=null): array {
		$result = [];
		foreach( $this->arguments as $k => $arg ){
			if( $arg['mandatory'] ){
				if( is_array($arguments) ){
					if( array_key_exists($k,$arguments) ){
						$result[$k] = $arguments[$k];
						continue;
					}
				}
				if( isset($input) ){
					$result[$k] = $input;
					continue;
				}
				throw new \Exception("Missing mandatory argument {$k} in ".get_class($this)." input: {$input}");
			}
			if( is_array($arguments) )
				$result[$k] = $arguments[$k] ?? $arg['default'];
			else
				$result[$k] = $arg['default'];
		}
		
		if( isset($input) ){
			$main = $this->getMainArgument();
			if( is_array($main) ){
				$arguments[ $main['name'] ] = $input;
			} else {
				throw new \Exception("Usage of pipe input without main argument in ".get_class($this));
			}
		}
		
		return $result;
	}
}

?>
