<?php

namespace Devworx\ViewHelper;

use Cascade\Runtime\Context;
use Cascade\Interfaces\INode;
use Cascade\Parser\Tokenizer;
use Cascade\Parser\NodeFactory;
use Cascade\Parser\ExpressionParser;

class IfViewHelper extends \Cascade\ViewHelper\AbstractViewHelper {
	
	public function initializeArguments(): void {
		$this->registerArgument('condition','string|bool|int|object|array|null',0,true,true);
		$this->registerArgument('then','string','',true);
		$this->registerArgument('else','string','',false);
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
			$arguments['condition'] = $this->prepare($arguments['condition']);
			$arguments['then'] = $this->prepare($arguments['then']);
			$arguments['else'] = array_key_exists('else',$arguments) ? $this->prepare($arguments['else']) : '';
			
			//echo \Devworx\Utility\DebugUtility::var_dump($arguments);
			
			if( $arguments['condition'] instanceof INode )
				$arguments['condition'] = $arguments['condition']->evaluate($context);
			
			if( $arguments['then'] instanceof INode )
				$arguments['then'] = $arguments['then']->evaluate($context);
			
			if( $arguments['else'] instanceof INode )
				$arguments['else'] = $arguments['else']->evaluate($context);
			
			return $arguments['condition'] ? $arguments['then'] : $arguments['else'];
		}
		
	}
	
}