<?php

namespace Cascade\Node;

use Cascade\Runtime\Context;
use Cascade\Parser\ParameterParser;
use Cascade\Parser\NodeFactory;
use Cascade\Enums\Token;
use Cascade\Interfaces\ITokenPattern;

class ArrayNode extends AbstractValueNode implements ITokenPattern
{
	public const PATTERN = [
		Token::NUMBER,
		Token::COLON,
		Token::VALUE,
		NodeFactory::PATTERN_OPTIONAL,
		[Token::COMMA,Token::NUMBER,Token::COLON,TOKEN::VALUE]
	];
	
    /**
     * @param array<int|string, AbstractNode> $items
	 * Struktur: key => value (beide AbstractNode)
     * Wenn key=null, dann numerisch indiziert
     */
    public function __construct(array $items = [])
    {
        $this->value = $items;
    }
	
	public static function getToken(): Token {
		return Token::ARRAY;
	}
	
	public static function matches(array $tokens): bool {
		return NodeFactory::checkTokenPattern(self::PATTERN,$tokens);
	}

    public static function fromTokens(array $tokens, int &$i = 0): ?self
	{
		$items = [];
		$count = count($tokens);
		$i = 0;

		while ($i < $count) {
			// Schlüssel: identifier vor dem :
			if (!isset($tokens[$i + 2])) return null;

			[$keyType, $keyVal] = $tokens[$i];
			[$colonType, $colonVal] = $tokens[$i + 1];

			if ($colonType !== Token::COLON) return null;

			$keyNode = NodeFactory::fromTokens([[$keyType, $keyVal]]);
			if (!$keyNode) return null;

			$i += 2;

			// Jetzt den vollständigen Value-Expression einsammeln
			$valueTokens = [];
			$depth = 0;

			while ($i < $count) {
				[$type, $val] = $tokens[$i];

				// Beenden, wenn Komma außerhalb von Klammern
				if ($type === Token::COMMA && $depth === 0) {
					break;
				}

				// Klammern tiefer zählen (für z.B. pow(...))
				if ($type === Token::OPEN_PAREN) $depth++;
				if ($type === Token::CLOSE_PAREN) $depth--;

				$valueTokens[] = [$type, $val];
				$i++;
			}

			$valueNode = NodeFactory::fromTokens($valueTokens);
			if (!$valueNode) return null;

			$items[] = [$keyNode, $valueNode];

			// Komma überspringen
			if (isset($tokens[$i]) && $tokens[$i][0] === Token::COMMA) {
				$i++;
			}
		}

		return new self($items);
	}


    public function evaluate(Context|array &$context,mixed $input=null): mixed
    {
        $result = [];
        foreach ($this->value as [$keyNode, $valueNode]) {
            $key = $keyNode->evaluate($context);
            $value = $valueNode->evaluate($context);
            $result[$key] = $value;
        }
		
		if( $input === null )
			return $result;
		if( $input instanceof INode )
			$input = $input->evaluate($context);
		
		if( is_array($input) )
			return array_merge($result,$input);
		
		return array_merge($result,[$input]);
		//throw new \Exception("Invalid pipe input of type ".gettype($input)." for " . get_class($this));
		//return $result;
    }

    public function compile(string $contextVar = '$context',string $input='null'): string
    {
        $compiled = [];
        foreach ($this->value as [$keyNode, $valueNode]) {
            $compiled[] = $keyNode->compile($contextVar) . ' => ' . $valueNode->compile($contextVar);
        }
		
		$compiled = implode(', ', $compiled);
		
		if( empty($input) || ( $input === 'null' ) )
			return "[{$compiled}]";
		
		if( ( str_starts_with($input,'[') && str_ends_with($input,']') ) )
			return "array_merge([{$compiled}],{$input})";
		
		return "array_merge([{$compiled}],[{$input}])";
    }
	
	public static function getValueType(): string {
		return Token::ARRAY->value;
	}
}
