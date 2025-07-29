<?php

namespace Devworx\ViewHelper\Format;

use Cascade\Runtime\Context;

class CurrencyViewHelper extends NumberViewHelper {
	
	public function initializeArguments(): void {
		parent::initializeArguments();
		$this->registerArgument('symbol','string','&euro;');
	}
	
	public function render(Context|array $context,array $arguments=null,mixed $input=null): mixed {
		$arguments = $this->mergeArguments($arguments,$input);
		return parent::render($context,$arguments,$input) . ' ' . $arguments['symbol'];
	}
	
}