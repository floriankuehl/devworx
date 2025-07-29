<?php

namespace Devworx\ViewHelper;

use Cascade\Runtime\Context;
use Cascade\Interfaces\INode;
use Cascade\Parser\Tokenizer;
use Cascade\Parser\NodeFactory;
use Cascade\Parser\ExpressionParser;

class ForViewHelper extends \Cascade\ViewHelper\AbstractViewHelper {
	
	public function initializeArguments(): void {
		$this->registerArgument('each','array|object',0,true,true);
		$this->registerArgument('as','string','',true);
		$this->registerArgument('key','string','');
		$this->registerArgument('iteration','string','');
	}
	
	private function prepare( string $expression ): mixed {
		$tokens = Tokenizer::tokenize( $expression );
		if( count($tokens) === 1 ){
			if( is_array($tokens[0][1]) )
				return NodeFactory::fromTokens( $tokens[0][1] );
			return $tokens[0][1];
		}
		return NodeFactory::fromTokens( $tokens );
	}
	
	public function render(Context|array $context,array $arguments=null,mixed $input=null): mixed {
		$arguments = $this->mergeArguments($arguments,$input);
		
		if( is_array($context) ){
			throw new \Exception("array context not implemented yet");
		} else {
						
			echo \Devworx\Utility\DebugUtility::var_dump($arguments);
		
		}
		return '';
	}
	
}