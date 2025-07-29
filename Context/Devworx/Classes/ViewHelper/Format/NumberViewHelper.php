<?php

namespace Devworx\ViewHelper\Format;

use Cascade\Runtime\Context;

class NumberViewHelper extends \Cascade\ViewHelper\AbstractViewHelper {
	
	public function initializeArguments(): void {
		$this->registerArgument('value','int|float',0,true,true);
		$this->registerArgument('decimals','int',2);
		$this->registerArgument('decimalseparator','string',',');
		$this->registerArgument('thousandsseparator','string','.');
	}
	
	public function render(Context|array $context,array $arguments=null,mixed $input=null): mixed {
		$arguments = $this->mergeArguments($arguments,$input);
		
		return number_format(
			floatval($arguments['value']),
			$arguments['decimals'],
			$arguments['decimalseparator'],
			$arguments['thousandsseparator']
		);
	}
	
}