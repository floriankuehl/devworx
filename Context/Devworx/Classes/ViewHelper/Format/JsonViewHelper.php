<?php

namespace Devworx\ViewHelper\Format;

use Cascade\Runtime\Context;

class JsonViewHelper extends \Cascade\ViewHelper\AbstractViewHelper {
	
	public function initializeArguments(): void {
		$this->registerArgument('value','array',null,true,true);
	}
	
	public function render(Context|array $context,array $arguments=null,mixed $input=null): mixed {
		$arguments = $this->mergeArguments($arguments,$input);
		
		return json_encode($arguments['value']);
	}
	
}