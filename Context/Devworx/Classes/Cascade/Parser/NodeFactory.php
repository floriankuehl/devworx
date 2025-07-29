<?php

namespace Cascade\Parser;

use Cascade\Interfaces\ITokenPattern;
use Cascade\Interfaces\INode;
use Cascade\Interfaces\IValueNode;

use Cascade\Node\HTMLNode;
use Cascade\Node\StringNode;
use Cascade\Node\NumberNode;
use Cascade\Node\ConstantNode;
use Cascade\Node\ArrayNode;
use Cascade\Node\ObjectNode;

use Cascade\Node\ViewHelperNode;
use Cascade\Node\FunctionCallNode;
use Cascade\Node\VariableNode;
use Cascade\Node\AssignmentNode;

use Cascade\Node\PipeChainNode;
use Cascade\Node\ExpressionNode;
use Cascade\Node\ConditionNode;

use Cascade\Enums\Token;

class NodeFactory
{
    public const PATTERN_STRICT = 'STRICT';
    public const PATTERN_VARIADIC = 'VARIADIC';
	public const PATTERN_OPTIONAL = 'OPTIONAL';
	public const PATTERN_CHAIN = 'CHAIN';

    /**
     * Liste der registrierten Node-Klassen
     *
     * @var class-string<INode>[]
     */
    protected static array $registeredNodes = [
		ViewHelperNode::class,
		FunctionCallNode::class,
		AssignmentNode::class,
		ConstantNode::class,
		PipeChainNode::class
    ];
	
	/**
     * Liste der registrierten Node-Klassen
     *
     * @var class-string<INode>[]
     */
    protected static array $registeredValueNodes = [
		ViewHelperNode::class,
		FunctionCallNode::class,
		ObjectNode::class,
		ArrayNode::class,
		//ExpressionNode::class,
    ];

    /**
     * Entry Point: erzeugt passende Node aus Tokens
     *
     * @param array $tokens
     * @return INode|null
     */
    public static function fromTokens(array $tokens, bool $expression=true): ?INode
    {		
		if( self::isPipeChain($tokens) ){
			return PipeChainNode::fromTokens($tokens);
		}
		
		if( count($tokens) === 1 ){
			[$type,$value] = $tokens[0];
			if( is_array($value) ){
				return match($type){
					Token::OBJECT => ObjectNode::fromTokens($value),
					Token::ARRAY => ArrayNode::fromTokens($value),
					Token::FUNCTION => FunctionCallNode::fromTokens($value),
					Token::VIEWHELPER => ViewHelperNode::fromTokens($value),
					default => ExpressionNode::fromTokens($value)
				};
			}
			
			return match($type){
				Token::NUMBER => NumberNode::fromTokens($tokens),
				Token::STRING => StringNode::fromTokens($tokens),
				Token::CONSTANT => ConstantNode::fromTokens($tokens),
				default => ExpressionNode::fromTokens($tokens)
			};
		}
	
        foreach (self::$registeredNodes as $nodeClass) {
			if (class_exists($nodeClass) && is_subclass_of($nodeClass, ITokenPattern::class)) {
				$match = self::checkTokenPattern($tokens,$nodeClass::PATTERN);
				/*
				if( 
					( $tokens[0][0] === Token::LT ) && 
					( $nodeClass === HTMLNode::class ) 
				){
					echo \Devworx\Utility\DebugUtility::var_dump([
						'pattern' => $nodeClass::PATTERN,
						'tokens' => $tokens,
						'match' => $match
					],$nodeClass,__METHOD__,__LINE__);
				}
				*/
				
				//$match = $nodeClass::matches($tokens);
				if( $match['matched'] ){
					//echo \Devworx\Utility\DebugUtility::var_dump($tokens,'Found '.$nodeClass.' from Pattern');
					return $nodeClass::fromTokens($tokens);
				}
				continue;
            }
			throw new \Exception("{$nodeClass} is no pattern class");
        }

		if( $expression ){
			//echo \Devworx\Utility\DebugUtility::var_dump($tokens,'Forging Expression',__METHOD__,__LINE__);
			return ExpressionNode::fromTokens($tokens);
		}
		
		//echo \Devworx\Utility\DebugUtility::var_dump($tokens,'WTF?',__METHOD__,__LINE__);
		return null;
    }

    /**
     * Prüft ein Pattern gegen eine gegebene Liste von Nodes.
     *
     * @param string[] $pattern
     * @param INode[] $nodes
     * @return bool
     */
    public static function checkPattern(array $pattern, array $nodes): bool
	{
		$patternCount = count($pattern);
		$nodeCount = count($nodes);

		$i = 0; // Pattern index
		$j = 0; // Node index

		while ($i < $patternCount) {
			$expected = $pattern[$i];

			// VARIADIC
			if ($expected === self::PATTERN_VARIADIC) {
				$i++;
				if ($i >= $patternCount) return false;

				$sub = $pattern[$i];

				if (is_array($sub)) {
					while ($j < $nodeCount && self::checkPattern($sub, array_slice($nodes, $j, count($sub)))) {
						$j += count($sub);
					}
				} else {
					while ($j < $nodeCount && self::matchesType($nodes[$j], $sub)) {
						$j++;
					}
				}

				$i++;
				continue;
			}

			// OPTIONAL
			if ($expected === self::PATTERN_OPTIONAL) {
				$i++;
				if ($i >= $patternCount) return false;

				$sub = $pattern[$i];

				if (is_array($sub)) {
					if (self::checkPattern($sub, array_slice($nodes, $j, count($sub)))) {
						$j += count($sub);
					}
				} else {
					if ($j < $nodeCount && self::matchesType($nodes[$j], $sub)) {
						$j++;
					}
				}

				$i++;
				continue;
			}

			// STRICT – analog wie bisher
			if ($expected === self::PATTERN_STRICT) {
				$i++;
				if ($i >= $patternCount || $j + 1 >= $nodeCount) return false;
				if (!self::matchesType($nodes[$j + 1], $pattern[$i])) return false;

				$i++;
				$j += 2;
				continue;
			}
			
			// CHAIN: Rest der Nodes wird gegen Subpattern geprüft
			if ($expected === self::PATTERN_CHAIN) {
				$i++;
				$subPattern = $pattern[$i] ?? [];
				return self::checkPattern($subPattern, array_slice($nodes, $j));
			}

			// Subpattern als Array
			if (is_array($expected)) {
				if (!self::checkPattern($expected, array_slice($nodes, $j, count($expected)))) {
					return false;
				}
				$j += count($expected);
				$i++;
				continue;
			}

			// Normaler Klassenvergleich
			if (!isset($nodes[$j]) || !self::matchesType($nodes[$j], $expected)) {
				return false;
			}

			$i++;
			$j++;
		}

		return $j === $nodeCount;
	}

	
	public static function isPipeChain(array $tokens): bool
	{
		if (empty($tokens)) {
			return false;
		}

		// Frühzeitig ausschließen: muss mindestens "a | b" sein (3 Tokens)
		if (count($tokens) < 3) {
			return false;
		}

		$pipeFound = false;

		for ($i = 0; $i < count($tokens); $i++) {
			$token = $tokens[$i];
			$type = $token[0];

			// Wenn Pipe-Operator gefunden wird
			if ($type === Token::PIPE) {
				$pipeFound = true;

				// Muss links und rechts ein Ausdruck sein
				if ($i === 0 || $i === count($tokens) - 1) {
					return false;
				}

				$left = $tokens[$i - 1][0] ?? null;
				$right = $tokens[$i + 1][0] ?? null;

				// Sicherheitsprüfung: links und rechts sollten keine weiteren Pipes oder Trennzeichen sein
				if ($left === Token::PIPE || $right === Token::PIPE) {
					return false;
				}
			}
		}

		return $pipeFound;
	}


	public static function checkTokenPattern(array $tokens, array $pattern): array
	{
		$tokenCount = count($tokens);
		$patternCount = count($pattern);

		$i = 0; // Pattern-Index
		$j = 0; // Token-Index

		while ($i < $patternCount) {
			$expected = $pattern[$i];

			if (is_string($expected)) {
				switch ($expected) {
					case self::PATTERN_STRICT:
						$i++;
						if ($i >= $patternCount || $j >= $tokenCount) {
							return ['matched' => false, 'consumed' => 0];
						}
						if (!self::isTokenArray($tokens[$j]) || !self::matchesTokenType($tokens[$j], $pattern[$i])) {
							return ['matched' => false, 'consumed' => 0];
						}
						$i++;
						$j++;
						break;

					case self::PATTERN_CHAIN:
						$i++;
						$subPattern = $i < $patternCount ? $pattern[$i] : [];

						// Voraussetzung: Mindestens ein Token::PIPE muss enthalten sein!
						$hasPipe = false;
						foreach (array_slice($tokens, $j) as $token) {
							$hasPipe = self::isTokenArray($token) && $token[0] === Token::PIPE;
							if ( $hasPipe ) break;
						}

						if (!$hasPipe) {
							return ['matched' => false, 'consumed' => 0];
						}

						// Danach wie gehabt
						$subMatch = self::checkTokenPattern(array_slice($tokens, $j), $subPattern);
						if (!$subMatch['matched']) {
							return ['matched' => false, 'consumed' => 0];
						}

						$j += $subMatch['consumed'];
						return ['matched' => true, 'consumed' => $j];

						
					case self::PATTERN_VARIADIC:
						$i++;
						if ($i >= $patternCount) {
							return ['matched' => false, 'consumed' => 0];
						}
						$sub = $pattern[$i];

						if (is_array($sub)) {
							while ($j < $tokenCount) {
								$subMatch = self::checkTokenPattern(array_slice($tokens, $j), $sub);
								if (!$subMatch['matched'] || $subMatch['consumed'] === 0) {
									break;
								}
								$j += $subMatch['consumed'];
							}
						} elseif (is_string($sub) || is_int($sub)) {
							while ($j < $tokenCount && self::isTokenArray($tokens[$j]) && self::matchesTokenType($tokens[$j], $sub)) {
								$j++;
							}
						} else {
							return ['matched' => false, 'consumed' => 0];
						}
						$i++;
						break;

					case self::PATTERN_OPTIONAL:
						$i++;
						if ($i >= $patternCount) {
							return ['matched' => false, 'consumed' => 0];
						}
						$sub = $pattern[$i];

						if (is_array($sub)) {
							$subMatch = self::checkTokenPattern(array_slice($tokens, $j), $sub);
							if ($subMatch['matched']) {
								$j += $subMatch['consumed'];
							}
						} elseif (is_string($sub) || is_int($sub)) {
							if ($j < $tokenCount && self::isTokenArray($tokens[$j]) && self::matchesTokenType($tokens[$j], $sub)) {
								$j++;
							}
						} else {
							return ['matched' => false, 'consumed' => 0];
						}
						$i++;
						break;

					default:
						return ['matched' => false, 'consumed' => 0];
				}
			} elseif (is_array($expected)) {
				$subMatch = self::checkTokenPattern(array_slice($tokens, $j), $expected);
				if (!$subMatch['matched']) {
					return ['matched' => false, 'consumed' => 0];
				}
				$j += $subMatch['consumed'];
				$i++;
			} else {
				if (!isset($tokens[$j]) || !self::isTokenArray($tokens[$j]) || !self::matchesTokenType($tokens[$j], $expected)) {
					return ['matched' => false, 'consumed' => 0];
				}
				$i++;
				$j++;
			}
		}

		return ['matched' => true, 'consumed' => $j];
	}



	protected static function isTokenArray($token): bool
	{
		return is_array($token) && count($token) >= 1;
	}



    /**
     * Prüft ob ein Node mit dem erwarteten Typ übereinstimmt.
     *
     * @param INode $node
     * @param Token $expectedType
     * @return bool
     */
    protected static function matchesType(INode $node, Token $expectedType): bool
    {
        if ($expectedType === Token::VALUE) {
            return $node instanceof IValueNode;
        }
		
		if($expectedType === Token::OPERATOR){
			return $node instanceof ExpressionNode;
		}

        return $node::getToken() === $expectedType;
    }
	
	/**
     * Prüft ob ein Token mit dem erwarteten Typ übereinstimmt.
     *
     * @param array $token
     * @param Token $expectedType
     * @return bool
     */
	protected static function matchesTokenType(array $token, Token $expected): bool
	{
		if ($expected === Token::VALUE) {
			return in_array($token[0], Tokenizer::VALUES, true);
		}
		
		if( $expected === Token::OPERATOR ){
			return in_array($token[0], Tokenizer::OPERATORS, true);
		}

		return $token[0] === $expected;
	}
	
	private static function matchAndCountTokens(array $tokens, array $pattern): int|false
	{
		$tokenCount = count($tokens);
		$patternCount = count($pattern);
		$i = 0; $j = 0;

		while ($i < $patternCount) {
			$expected = $pattern[$i];

			if (is_string($expected)) {
				switch ($expected) {
					case self::PATTERN_STRICT:
						$i++;
						if ($i >= $patternCount || $j >= $tokenCount) return false;
						if (!self::isTokenArray($tokens[$j]) || !self::matchesTokenType($tokens[$j], $pattern[$i])) return false;
						$i++; $j++;
						break;

					default:
						// handle other directives if needed
						return false;
				}
			} elseif (is_array($expected)) {
				$subConsumed = self::matchAndCountTokens(array_slice($tokens, $j), $expected);
				if ($subConsumed === false) return false;
				$j += $subConsumed;
				$i++;
			} else {
				if (!isset($tokens[$j]) || !self::isTokenArray($tokens[$j]) || !self::matchesTokenType($tokens[$j], $expected)) {
					return false;
				}
				$i++; $j++;
			}
		}

		return $j;
	}


    /**
     * Optional: Tokens aus Ausdrucksstring parsen
     *
     * @param string $expression
     * @return INode|null
     */
    public static function fromString(string $expression): ?INode
    {
        $tokens = Tokenizer::tokenize($expression);
        return self::fromTokens($tokens);
    }
}
