<?php

namespace Devworx\ViewHelper;

use Cascade\Runtime\Context;

class SetViewHelper extends \Cascade\ViewHelper\AbstractViewHelper {
	
	public function initializeArguments(): void {
		$this->registerArgument('value','int|float|string|bool|array|object|null',0,true,true);
		$this->registerArgument('name','string','',true);
	}
	
	public function render(Context|array $context,array $arguments=null,mixed $input=null): mixed {
		$arguments = $this->mergeArguments($arguments,$input);
		
		if( is_array($context) ){
			throw new \Exception("array context not implemented yet");
		} else {
			$context->set($arguments['name'],$arguments['value']);
			return true;
		}
		return false;
	}
	
}