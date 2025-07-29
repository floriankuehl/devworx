<?php

namespace Cascade\Node;

use Cascade\Enums\Token;
use Cascade\Interfaces\INode;
use Cascade\Parser\ParameterParser;
use Cascade\Runtime\ViewHelperInvoker;
use Cascade\Runtime\Context;

class HTMLViewHelperNode extends AbstractNode
{
	protected string $helperName;
	protected array $arguments;
	protected array $children;

	public function __construct(string $helperName, array $arguments = [], array $children = [])
	{
		$this->helperName = $helperName;
		$this->arguments = $arguments;
		$this->children = $children;
	}

	public static function getToken(): Token {
		return Token::VIEWHELPER;
	}

	public static function fromTokens(array $tokens, int &$i=0): ?self
	{
		$start = $i;
		if (
			$tokens[$i][0] !== Token::LT ||
			!isset($tokens[$i + 1], $tokens[$i + 2]) ||
			$tokens[$i + 1][0] !== Token::IDENTIFIER ||
			$tokens[$i + 2][0] !== Token::COLON
		) {
			return null;
		}

		$namespace = $tokens[$i + 1][1];
		$pos = $i + 3;

		$helperNameParts = [];
		while (isset($tokens[$pos])) {
			if( $tokens[$pos][0] === Token::IDENTIFIER ){
				$helperNameParts[] = $tokens[$pos][1];
				$pos++;	
				continue;
			}
			if ($tokens[$pos][0] === Token::DOT) {
				$pos++;
				continue;
			}
			
			break;
		}

		$helperName = $namespace . ':' . implode('.', $helperNameParts);

		// Attribute parsen
		[$attributes, $pos] = ParameterParser::parseAttributes($tokens, $pos);

		// Self-closing Tag?
		if (
			isset($tokens[$pos], $tokens[$pos + 1]) &&
			$tokens[$pos][0] === Token::DIV &&
			$tokens[$pos + 1][0] === Token::GT
		) {
			$i = $pos + 2;
			return new self($helperName, $attributes, []);
		}

		if (!isset($tokens[$pos]) || $tokens[$pos][0] !== Token::GT) {
			return null; // Ungültige Syntax
		}

		$pos++; // Nach dem '>'
		$i = $pos;

		// Sammle Body-Knoten (bis schließendes </namespace:helper>)
		$bodyNodes = [];
		while ($i < count($tokens)) {
			if (
				$tokens[$i][0] === Token::LT &&
				isset($tokens[$i + 1], $tokens[$i + 2], $tokens[$i + 3]) &&
				$tokens[$i + 1][0] === Token::DIV &&
				$tokens[$i + 2][1] === $namespace &&
				$tokens[$i + 3][0] === Token::COLON
			) {
				// Matches </namespace:helper>
				$endPos = $i + 4;
				$closingParts = [];
				while (isset($tokens[$endPos]) && in_array($tokens[$endPos][0], [Token::IDENTIFIER, Token::DOT])) {
					if ($tokens[$endPos][0] === Token::DOT) {
						$endPos++;
						continue;
					}
					$closingParts[] = $tokens[$endPos][1];
					$endPos++;
				}
				$closingName = implode('.', $closingParts);
				if ($closingName === implode('.', $helperNameParts)) {
					// Skip to >
					while (isset($tokens[$endPos]) && $tokens[$endPos][0] !== Token::GT) {
						$endPos++;
					}
					$i = $endPos + 1;
					break;
				}
			}

			$bodyNodes[] = \Cascade\Parser\HTMLParser::parse($tokens, $i);
		}

		// Flache Struktur in children konsolidieren
		$flattened = [];
		foreach ($bodyNodes as $nodeGroup) {
			foreach ($nodeGroup as $n) {
				$flattened[] = $n;
			}
		}

		return new self($helperName, $attributes, $flattened);
	}

	public function evaluate(Context|array &$context, mixed $input = null): mixed
	{
		$evaluatedArgs = [];

		foreach ($this->arguments as $key => $argNode) {
			$evaluatedArgs[$key] = $argNode instanceof INode ? $argNode->evaluate($context) : $argNode;
		}

		if (!isset($evaluatedArgs['children'])) {
			$evaluatedArgs['children'] = $this->evaluateChildren($context);
		}

		return ViewHelperInvoker::invoke($context, $this->helperName, $evaluatedArgs, $input);
	}

	private function evaluateChildren(Context|array &$context): string
	{
		$output = '';
		foreach ($this->children as $child) {
			if ($child instanceof INode) {
				$output .= $child->evaluate($context);
			}
		}
		return $output;
	}

	public function compile(string $contextVar = '$context', string $input = 'null'): string
	{
		$compiledArgs = [];
		foreach ($this->arguments as $argName => $argNode) {
			
			$argValue = is_string($argNode) ? 
				var_export($argNode,true) : 
				$argNode->compile($contextVar);
			
			$compiledArgs[] = var_export($argName, true) . ' => ' . $argValue;
		}

		if (!isset($this->arguments['children'])) {
			$compiledChildren = '';
			foreach ($this->children as $child) {
				if ($child instanceof INode) {
					$compiledChildren .= ' . ' . $child->compile($contextVar);
				}
			}
			$compiledArgs[] = "'children' => ''" . $compiledChildren;
		}

		$argsString = '[' . implode(', ', $compiledArgs) . ']';
		$funcName = var_export($this->helperName, true);

		return ViewHelperInvoker::class . "::invoke($contextVar, $funcName, $argsString, $input)";
	}
}
