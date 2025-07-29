<?php

namespace Cascade\Utility;

use Cascade\Runtime\Context;
use Cascade\Parser\Tokenizer;
use Cascade\Parser\NodeFactory;

use Cascade\Interfaces\INode;
use Cascade\Node\PipeChainNode;

class CascadeUtility {
	
	static function dumpNode(Context $context, INode $node, string $label,mixed $input=null): string {
		
		return implode(PHP_EOL,[
			'<li class="list-group-item list-group-item-success">',
				( $node ? get_class($node) : 'n/a' ),
				'<ul class="list-group">',
					'<li class="list-group-item list-group-item-info">',
						\Devworx\Utility\DebugUtility::var_dump(
							[
								'creates' => $input, 
								'from' => $node->dump()
							],
							"{$label} Evaluation",
							__METHOD__,
							__LINE__
						),
					'</li>',
				'</ul>',
			'</li>'
		]);
	}
	
	static function test(Context $context, $cmd, $type, array &$plots=null){
		if( $plots === null ) $plots = [];
		
		$tokens = is_array($cmd) ? $cmd : Tokenizer::tokenize($cmd);
		$node = NodeFactory::fromTokens($tokens);
		
		$class = $node ? 'success' : 'danger';
		
		$lines = [
			'<ul class="list-group">',
			'<li class="list-group-item list-group-item-'.$class.'">',
				$node ? get_class($node) : 'n/a',
		];
				
		if( $node instanceof PipeChainNode ){
			
			//return \Devworx\Utility\DebugUtility::var_dump($node->getNodes());
			
			$tmp = [];
			$log = [];
			foreach( $node->getNodes() as $i => $n ){
				if( $i === 0 ){
					$input = $n->evaluate($context);
				} else {
					$input = $n->evaluate($context,$input);
				}
				$log []= $input;
				$tmp []= self::dumpNode($context,$n,$n::getToken()->name, $input);
			}
			
			$lines []= '<li class="list-group-item"><devworx-debug><code>' . htmlspecialchars($cmd) . '</code></devworx-debug></li>';
			$log = implode(' -&gt; ',array_map('json_encode',$log));
			$lines []= '<li class="list-group-item"><code>' . htmlspecialchars($log) . '</code></li>';
			$lines []= '<li class="list-group-item"><code>' . htmlspecialchars($node->compile()) . '</code></li>';
			
			$lines[] = implode(PHP_EOL,[
				'<ul class="list-group list-group-horizontal">',
				...$tmp,
				'</ul>'
			]);
		} elseif( $node ){
			//$input = $node->evaluate($context);
			$input = 'TEST';
			
			if( is_array($cmd) ){
				$cmd = implode('',array_column($cmd,1));
			}
			
			$lines []= '<li class="list-group-item"><devworx-debug><code>' . htmlspecialchars( $cmd ) . '</code></devworx-debug></li>';
			$lines []= '<li class="list-group-item"><code>' . htmlspecialchars($node->compile('$context','null')) . '</code></li>';
			$lines []= self::dumpNode($context,$node,$node::getToken()->name,$input);
		} else {
			$lines []= '<ul class="list-group">';
			foreach ($tokens as $token) {
				$value = $token[1];
				if( is_array($value) )
					$value = self::test($context, $value,'expression',$plots);
				else
					$value = "<span class=\"text-primary px-3\">{$value}</span>";
				
				$class = $value === null ? 'list-group-item-secondary' : 'list-group-item-info';
				$lines []= '<li class="list-group-item '.$class.'">' . 
					'<span>'.$token[0]->name.'</span>' . 
					$value . 
				'</li>';
			}
			$lines []= '</ul>';
		}
		$lines []= '</li>';
		$lines []= '</ul>';
		
		$plots[$type][] = [
			'tokens' => $tokens,
			'node' => $node === null ? 'n/a' : get_class($node),
			'dump' => $node === null ? $node : $node->dump(),
		];
		return implode(PHP_EOL,$lines);
	}
	
	
}