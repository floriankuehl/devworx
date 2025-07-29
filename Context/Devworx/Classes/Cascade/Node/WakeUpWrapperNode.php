<?php

namespace Cascade\Node;

use Cascade\Parser\TemplateParser;
use Cascade\Parser\NodeFactory;
use Cascade\Runtime\ViewHelperInvoker;
use Cascade\Parser\ExpressionParser;

class WakeUpWrapperNode extends AbstractNode
{
    public function __construct(protected string $rawContent) {}

	public static function getToken(): Token {
		return Token::WAKE;
	}

    public function compile(string $contextVar = '$context'): string
    {
        // Zerlege nach "<f:...>...</f:...>" und parsen diese Teile normal
        // z. B. mit regex: /<([a-zA-Z]+:[a-zA-Z0-9]+)\s*(.*?)>(.*?)<\/\1>/
        
        $parsed = preg_replace_callback('/<([a-zA-Z]+:[a-z0-9_\.]+)(.*?)>(.*?)<\/\1>/si', function ($match) use ($contextVar) {
			$viewHelperName = $match[1];
            $args = $match[2];
            $content = $match[3];
            
            $inner = (new TemplateParser())->parse($content);
            $compiledInner = $inner->compile($contextVar);

            return ViewHelperInvoker::invoke($viewHelperName, $compiledInner, $args, $contextVar);
        }, $this->rawContent);

        return var_export($parsed, true); // Als String literal
    }
	
	public static function fromTokens(array $tokens, int &$i=0): ?self
	{
		// WakeUpWrapperNode kapselt anderen Ausdruck wie {f:format.raw(...)}

		if (count($tokens) === 1 && $tokens[0]['type'] === 'EXPRESSION') {
			$inner = trim($tokens[0]['value'], '{} ');
			$innerTokens = (new ExpressionParser())->tokenize($inner);
			$node = NodeFactory::fromTokens($innerTokens);
			return $node ? new self($node) : null;
		}

		return null;
	}
	
	public function evaluate(Context|array $context): string
	{
		// Kopiere den ursprünglichen Content
		$content = $this->rawContent;

		// Suche nach <f:...>...</f:...>
		$content = preg_replace_callback(
			'/<([a-zA-Z]+:[a-z0-9_\.]+)(.*?)>(.*?)<\/\1>/si',
			function ($match) use ($context) {
				$viewHelperName = $match[1];
				$argsString = $match[2];
				$innerContent = $match[3];

				// Parse das innere Template (rekursiv)
				$innerNode = (new TemplateParser())->parse($innerContent);
				$evaluatedInner = $innerNode->evaluate($context);

				// Konvertiere Attributstring in Argumente-Array
				$args = [];
				preg_match_all('/(\w+)="([^"]*)"/', $argsString, $argMatches, PREG_SET_ORDER);
				foreach ($argMatches as $argMatch) {
					$args[$argMatch[1]] = $argMatch[2];
				}

				// Übergib an ViewHelperInvoker
				return ViewHelperInvoker::invoke($viewHelperName, $evaluatedInner, $args, $context);
			},
			$content
		);

		return $content;
	}

}
