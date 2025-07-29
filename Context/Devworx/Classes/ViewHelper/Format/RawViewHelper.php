<?php

namespace Devworx\ViewHelper\Format;

use Cascade\Runtime\Context;

class RawViewHelper extends \Cascade\ViewHelper\AbstractViewHelper {
	
	public function initializeArguments(): void {
		$this->registerArgument('value','int|string|bool|null',0,true,true);
	}
	
	public function render(Context|array $context,array $arguments=null,mixed $input=null): mixed {
		$arguments = $this->mergeArguments($arguments,$input);
		
		return $arguments['value'];
	}
	
}