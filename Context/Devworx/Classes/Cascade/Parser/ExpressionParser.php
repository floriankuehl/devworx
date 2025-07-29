<?php

namespace Cascade\Parser;

use Cascade\Enums\Token;

use Cascade\Interfaces\INode;
use Cascade\Node\AssignmentNode;
use Cascade\Node\UnaryOperatorNode;
use Cascade\Node\BinaryOperatorNode;
use Cascade\Node\TernaryNode;
use Cascade\Node\VariableNode;
use Cascade\Node\NumberNode;
use Cascade\Node\StringNode;
use Cascade\Node\FunctionCallNode;
use Cascade\Node\HTMLNode;
use Cascade\Node\HTMLViewHelperNode;

class ExpressionParser
{
	public static function parse(array $tokens, bool $preserveWhitespace=false): ?INode
    {
        if( empty($tokens) ) return null;
		
		if( !$preserveWhitespace ){
			// Whitespace entfernen, da wir hier mit "isolierten" Expressions arbeiten
			$tokens = array_filter($tokens, fn($t) => $t[0] !== Token::WHITESPACE);
			$tokens = array_values($tokens); // reindizieren
		}
		
		$tokens = self::trimParentheses($tokens);
		
		if( self::isHTMLViewHelper($tokens) ){
			return HTMLViewHelperNode::fromTokens($tokens);
		}
		
		if( self::isHTML($tokens) ){
			return HTMLNode::fromTokens($tokens);
		}
		
		if( self::isHTMLDoc($tokens) ){
			return HTMLNode::fromTokens($tokens);
		}
		
		if (self::isAssignment($tokens)) {
			return self::buildAssignment($tokens);
		}
		
		if (self::isFunctionCall($tokens)) {
			return self::buildFunctionCall($tokens);
		}
		
		// Variable Path?
		if (self::isPureVariablePath($tokens)) {
			return self::buildVariable( $tokens );
		}
		
		if( self::isPureString($tokens) ){
			return StringNode::fromTokens($tokens);
		}
		
		if( self::isPureNumber($tokens) ){
			return NumberNode::fromTokens($tokens);
		}	

        // Ternary?
        $questionIndex = self::findTokenOutsideParens($tokens, Token::TERNARY);
        if ($questionIndex !== null) {
            return self::buildTernary($tokens, $questionIndex);
        }

        // Unary?
        if (self::isUnary($tokens)) {
            return self::buildUnary($tokens);
        }
		
        // Binary operator mit niedrigster Präzedenz
        $opIndex = self::findLowestPrecedenceOperatorIndex($tokens);
        if ($opIndex > 0) {
            return self::buildBinary($tokens, $opIndex);
        }
		
		return NodeFactory::fromTokens($tokens,false);
    }
	
	protected static function isPureVariablePath(array $tokens): bool
	{
		if (empty($tokens)) {
			return false;
		}

		// Muss mit Identifier beginnen
		if ($tokens[0][0] !== Token::IDENTIFIER) {
			return false;
		}

		// Muss abwechselnd IDENTIFIER, DOT, IDENTIFIER, ...
		for ($i = 1; $i < count($tokens); $i++) {
			if ($i % 2 === 1 && $tokens[$i][0] !== Token::DOT) {
				return false;
			}
			if ($i % 2 === 0 && $tokens[$i][0] !== Token::IDENTIFIER) {
				return false;
			}
		}

		// Muss mit Identifier enden
		return $tokens[count($tokens) - 1][0] === Token::IDENTIFIER;
	}

	protected static function extractVariablePath(array $tokens): array
	{
		$path = [];

		$count = count($tokens);
		if ($count === 0) {
			return $path;
		}

		for ($i = 0; $i < $count; $i++) {
			$token = $tokens[$i];

			if ($i % 2 === 0) {
				// Gerade Indizes müssen IDENTIFIER sein
				if ($token[0] !== Token::IDENTIFIER) {
					throw new ParseException("Expected identifier at position $i");
				}
				$path[] = $token[1];
			} else {
				// Ungerade Indizes müssen DOT sein
				if ($token[0] !== Token::DOT) {
					throw new ParseException("Expected '.' at position $i");
				}
			}
		}

		return $path;
	}
	
	public static function isHTMLViewHelper(array $tokens): bool {
		return ( $tokens[0][0] === Token::LT ) &&
			isset($tokens[1], $tokens[2], $tokens[3]) && 
			( $tokens[1][0] === Token::IDENTIFIER ) &&
			( $tokens[2][0] === Token::COLON ) &&
			( $tokens[3][0] === Token::IDENTIFIER );
	}
	
	public static function isHTML(array $tokens): bool {
		return ( $tokens[0][0] === Token::LT ) &&
			isset($tokens[1]) && ( $tokens[1][0] === Token::IDENTIFIER );
	}
	
	public static function isHTMLDoc(array $tokens): bool {
		return ( $tokens[0][0] === Token::LT ) &&
			isset($tokens[1], $tokens[2]) && 
			( $tokens[1][0] === Token::NOT ) &&
			( $tokens[2][0] === Token::IDENTIFIER );
	}
	
	protected static function isPure(Token $token,array $tokens){
		return ( count($tokens) == 1 ) && ( $tokens[0][0] === $token );
	}
	
	public static function isPureNumber(array $tokens): bool {
		return self::isPure(Token::NUMBER,$tokens);
	}
	
	public static function isPureString(array $tokens): bool {
		return self::isPure(Token::STRING,$tokens);
	}
	
	public static function isFunctionCall(array $tokens): bool {
		if (count($tokens) < 3) return false;

		if ($tokens[0][0] === Token::IDENTIFIER && $tokens[1][0] === Token::OPEN_PAREN) {
			$closing = self::findClosingParenIndex($tokens, 1);
			return $closing !== null;
		}

		return false;
	}
	
	public static function isAssignment(array $tokens): bool {
		if (count($tokens) < 2) return false;
		foreach( $tokens as $token ){
			if( $token[0] === Token::ASSIGN )
				return true;
		}
		return false;
	}
	
	public static function buildVariable(array $tokens){
		return VariableNode::fromTokens($tokens);
	}
	
	private static function buildAssignment(array $tokens): INode {
		return AssignmentNode::fromTokens($tokens);
	}
	
	protected static function buildFunctionCall(array $tokens): ?INode
	{
		if (count($tokens) < 3) return null;

		[$firstType, $firstValue] = $tokens[0];
		if ($firstType !== Token::IDENTIFIER) return null;

		// Funktion muss direkt gefolgt werden von (
		if ($tokens[1][0] !== Token::OPEN_PAREN) return null;

		// Argumente extrahieren
		$depth = 0;
		$endIndex = null;
		for ($i = 1; $i < count($tokens); $i++) {
			if ($tokens[$i][0] === Token::OPEN_PAREN) $depth++;
			elseif ($tokens[$i][0] === Token::CLOSE_PAREN) $depth--;

			if ($depth === 0) {
				$endIndex = $i;
				break;
			}
		}

		if ($endIndex === null) return null;

		$funcName = $firstValue;
		$argTokens = array_slice($tokens, 2, $endIndex - 2);
		$argLists = ParameterParser::splitArguments($argTokens);

		$arguments = [];
		foreach ($argLists as $argTokens) {
			$argNode = ExpressionParser::parse($argTokens);
			if (!$argNode) return null;
			$arguments[] = $argNode;
		}

		$callNode = new FunctionCallNode($funcName, $arguments);

		// Wenn noch Tokens danach folgen (wie * 100), weiterparsen
		$remaining = array_slice($tokens, $endIndex + 1);
		if (!empty($remaining)) {
			$combinedTokens = array_merge(
				[[Token::IDENTIFIER, '__FUNC_RESULT']], // Platzhalter für den linken Teil
				$remaining
			);

			$leftNode = $callNode;
			$parsed = ExpressionParser::parse($combinedTokens);

			if ($parsed instanceof BinaryOperatorNode) {
				// Setze manuell den linken Teil auf den eigentlichen callNode
				$parsed->setLeft($leftNode);
				return $parsed;
			}
		}

		return $callNode;
	}

	
	protected static function findMatchingParen(array $tokens, int $openIndex): int
	{
		$depth = 0;
		$len = count($tokens);

		for ($i = $openIndex; $i < $len; $i++) {
			[$type] = $tokens[$i];

			if ($type === Token::OPEN_PAREN) $depth++;
			elseif ($type === Token::CLOSE_PAREN) {
				$depth--;
				if ($depth === 0) return $i;
			}
		}

		throw new \RuntimeException("Unmatched parenthesis in tokens");
	}
	
	private static function findClosingParenIndex(array $tokens, int $openIndex): ?int {
		$depth = 0;
		$count = count($tokens);

		for ($i = $openIndex; $i < $count; $i++) {
			if ($tokens[$i][0] === Token::OPEN_PAREN) {
				$depth++;
			} elseif ($tokens[$i][0] === Token::CLOSE_PAREN) {
				$depth--;
				if ($depth === 0) {
					return $i;
				}
			}
		}

		return null; // Nicht gefunden
	}
	
	protected static function extractBetweenParens(array $tokens): array
	{
		$result = [];
		$depth = 0;
		$started = false;

		foreach ($tokens as $token) {
			if ($token[0] === Token::OPEN_PAREN) {
				$depth++;
				if (!$started) {
					$started = true;
					continue;
				}
			}

			if ($token[0] === Token::CLOSE_PAREN) {
				$depth--;
				if ($depth === 0) {
					break;
				}
			}

			if ($started) {
				$result[] = $token;
			}
		}

		if (!$started || $depth !== 0) {
			throw new ParseException("Unbalanced or missing parentheses in token stream.");
		}

		return $result;
	}

	protected static function parseArguments(array $tokens): array {
		$args = [];
		$i = 0;
		while ($i < count($tokens)) {
			if ($tokens[$i][0] === Token::CLOSE_PAREN) break;
			if ($i + 2 < count($tokens) && $tokens[$i][0] === Token::IDENTIFIER && $tokens[$i + 1][0] === Token::ASSIGN) {
				$name = $tokens[$i][1];
				$valueTokens = [];
				$i += 2;
				$depth = 0;
				while ($i < count($tokens)) {
					if ($tokens[$i][0] === Token::COMMA && $depth === 0) {
						$i++;
						break;
					} elseif ($tokens[$i][0] === Token::OPEN_PAREN) $depth++;
					elseif ($tokens[$i][0] === Token::CLOSE_PAREN && $depth-- === 0) break;
					$valueTokens[] = $tokens[$i++];
				}
				$args[$name] = self::parse($valueTokens);
			} else {
				$valueTokens = [];
				$depth = 0;
				while ($i < count($tokens)) {
					if ($tokens[$i][0] === Token::COMMA && $depth === 0) {
						$i++;
						break;
					} elseif ($tokens[$i][0] === Token::OPEN_PAREN) $depth++;
					elseif ($tokens[$i][0] === Token::CLOSE_PAREN && $depth-- === 0) break;
					$valueTokens[] = $tokens[$i++];
				}
				$args[] = self::parse($valueTokens);
			}
			if ($i < count($tokens) && $tokens[$i][0] === Token::COMMA) $i++;
		}
		return $args;
	}
	
	protected static function splitArguments(array $tokens): array
	{
		$args = [];
		$current = [];
		$depth = 0;

		foreach ($tokens as $token) {
			[$type, $value] = $token;
			if ($type === Token::COMMA && $depth === 0) {
				$args[] = $current;
				$current = [];
			} else {
				if ($type === Token::OPEN_PAREN) $depth++;
				elseif ($type === Token::CLOSE_PAREN) $depth--;
				$current[] = $token;
			}
		}
		if ($current) $args[] = $current;

		return $args;
	}


    protected static function trimParentheses(array $tokens): array
	{
		if (empty($tokens)) return $tokens;

		while (
			$tokens[0][0] === Token::OPEN_PAREN &&
			end($tokens)[0] === Token::CLOSE_PAREN &&
			self::fullyWrappedInParentheses($tokens)
		) {
			array_shift($tokens);
			array_pop($tokens);
		}

		return $tokens;
	}
	
	public static function parseMixed(string $value): array
	{
		$parts = preg_split('/({{.*?}}|{[^{}]+})/', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
		$nodes = [];

		foreach ($parts as $part) {
			if ($part === '' || $part === null) continue;

			if (preg_match('/^{(.*?)}$/s', $part, $matches)) {
				$expression = $matches[1];
				$tokens = Tokenizer::tokenize($expression);
				$node = self::parse($tokens);
				$nodes[] = $node ?? new StringNode($part);
			} else {
				$nodes[] = new StringNode($part); // whitespace etc. bleibt erhalten
			}
		}

		return $nodes;
	}
	
	protected static function fullyWrappedInParentheses(array $tokens): bool
	{
		$depth = 0;
		$count = count($tokens);

		// Beginnt mit ( und endet mit ) ist schon geprüft

		for ($i = 0; $i < $count; $i++) {
			[$type] = $tokens[$i];

			if ($type === Token::OPEN_PAREN) {
				$depth++;
			} elseif ($type === Token::CLOSE_PAREN) {
				$depth--;
			}

			// Wenn bei Tiefe 0 eine Klammer schließt, sind äußere Klammern **nicht** um alles drum herum
			if ($depth === 0 && $i < $count - 1) {
				return false;
			}
		}

		// Am Ende muss alles geschlossen sein
		return $depth === 0;
	}

    protected static function balancedParentheses(array $tokens): bool
    {
        $depth = 0;
        foreach ($tokens as [$type]) {
            if ($type === Token::OPEN_PAREN) {
                $depth++;
            } elseif ($type === Token::CLOSE_PAREN) {
                $depth--;
                if ($depth < 0) return false;
            }
        }
        return $depth === 0;
    }

    protected static function findLowestPrecedenceOperatorIndex(array $tokens): int
    {
        $minPrecedence = PHP_INT_MAX;
        $foundIndex = -1;
        $depth = 0;

        foreach ($tokens as $i => [$type, $value]) {
						
            if ($type === Token::OPEN_PAREN) {
                $depth++;
            } elseif ($type === Token::CLOSE_PAREN) {
                $depth--;
            } elseif ( Tokenizer::isOperator($type) && ( $depth === 0 ) ) {
				if( array_key_exists($type->value,Tokenizer::PRECEDENCE) ){
					$prec = Tokenizer::PRECEDENCE[$type->value] ?? 100;
					if ($prec <= $minPrecedence) {
						$minPrecedence = $prec;
						$foundIndex = $i;
					}
				}
            }
        }
        return $foundIndex;
    }

    protected static function findTokenOutsideParens(array $tokens, Token $target): ?int
    {
        $depth = 0;
        foreach ($tokens as $i => [$type, $value]) {
            if ($type === Token::OPEN_PAREN) $depth++;
            elseif ($type === Token::CLOSE_PAREN) $depth--;
            elseif ($depth === 0 && $type === $target) return $i;
        }
        return null;
    }
	
	protected static function extractOperandTokens(array $tokens): array
	{
		$result = [];
		$depth = 0;
		$ternaryDepth = 0;

		foreach ($tokens as $index => $token) {
			$result[] = $token;

			if ($token[0] === Token::OPEN_PAREN) {
				$depth++;
			} elseif ($token[0] === Token::CLOSE_PAREN) {
				if ($depth > 0) {
					$depth--;
				}
				if ($depth === 0 && $ternaryDepth === 0) {
					break; // Ausdruck komplett
				}
			} elseif ($token[0] === Token::TERNARY) {
				$ternaryDepth++;
			} elseif ($token[0] === Token::COLON) {
				if ($ternaryDepth > 0) {
					$ternaryDepth--;
				}
			} elseif ($depth === 0 && $ternaryDepth === 0) {
				// Wenn nächste Token ein Stop-Token ist, Ausdruck beenden
				$nextIndex = $index + 1;
				if (isset($tokens[$nextIndex])) {
					$nextTokenType = $tokens[$nextIndex][0];
					if (in_array($nextTokenType, Tokenizer::STOP_TOKENS, true)) {
						break;
					}
				} else {
					break;
				}
			}
		}

		return $result;
	}

    protected static function isUnary(array $tokens): bool
    {
        if (count($tokens) < 2) return false;
        return Tokenizer::isUnaryOperator($tokens[0][0]);
    }

   protected static function buildUnary(array $tokens): INode
	{
		$opLeft = Tokenizer::isUnaryOperator($tokens[0][0]);
		$opRight = isset($tokens[1]) && Tokenizer::isUnaryOperator($tokens[1][0]);
		
		if ($opLeft) {
			$operator = $tokens[0][0];
			$operandTokens = self::extractOperandTokens(array_slice($tokens, 1));
			$operand = self::parse($operandTokens);
		} elseif ($opRight) {
			$operator = $tokens[1][0];
			$operand = self::parse([$tokens[0]]);
		} else {
			throw new \RuntimeException("Unary operation without operator");
		}

		if ($operand === null) {
			throw new \RuntimeException("Unary operand missing with operator {$operator->value}");
		}

		return new UnaryOperatorNode($operator, $operand);
	}

	protected static function extractRightHand(array $tokens): ?array
	{
		$count = count($tokens);
		if ($count === 0) return null;

		$offset = 0;

		// Wenn mit Klammer beginnt, ganzen Block extrahieren
		if ($tokens[$offset][0] === Token::OPEN_PAREN) {
			$depth = 1;
			$offset++;

			while ($offset < $count && $depth > 0) {
				if ($tokens[$offset][0] === Token::OPEN_PAREN) {
					$depth++;
				} elseif ($tokens[$offset][0] === Token::CLOSE_PAREN) {
					$depth--;
				}
				$offset++;
			}

			// Gib den gesamten Block zurück
			return array_slice($tokens, 0, $offset);
		}

		// Fallback: Gib restliche Tokens zurück
		return $tokens;
	}

    protected static function buildBinary(array $tokens, int $opIndex): INode
    {
        $leftTokens = array_slice($tokens, 0, $opIndex);
        $rightTokens = self::extractRightHand(array_slice($tokens, $opIndex + 1));
		$operator = $tokens[$opIndex][0];
		
        $leftNode = self::parse($leftTokens);
        $rightNode = self::parse($rightTokens);
		if( $rightNode === null ){
			echo \Devworx\Utility\DebugUtility::var_dump([
				'op' => $operator->name,
				'leftNode' => $leftNode,
				'rightTokens' => $rightTokens
			],"Cant identifiy right side tokens",__METHOD__,__LINE__);
		}

        return new BinaryOperatorNode($leftNode, $operator, $rightNode);
    }

    protected static function buildTernary(array $tokens, int $questionIndex): INode
    {
        $colonIndex = null;
        $depth = 0;

        for ($i = $questionIndex + 1, $len = count($tokens); $i < $len; $i++) {
            [$type] = $tokens[$i];

            if ($type === Token::OPEN_PAREN) {
                $depth++;
            } elseif ($type === Token::CLOSE_PAREN) {
                $depth--;
            } elseif ($depth === 0 && $type === Token::COLON) {
                $colonIndex = $i;
                break;
            }
        }

        if ($colonIndex === null) {
            throw new \RuntimeException('Malformed ternary expression: missing colon');
        }

        $conditionTokens = array_slice($tokens, 0, $questionIndex);
        $trueTokens = array_slice($tokens, $questionIndex + 1, $colonIndex - $questionIndex - 1);
        $falseTokens = array_slice($tokens, $colonIndex + 1);

        return new TernaryNode(
            self::parse($conditionTokens),
            self::parse($trueTokens),
            self::parse($falseTokens)
        );
    }
	
	
	
	
}
