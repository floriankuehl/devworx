<?php

namespace Cascade\Node;

use Cascade\Runtime\Context;
use Cascade\Enums\Token;

use Cascade\Parser\NodeFactory;
use Cascade\Interfaces\ITokenPattern;

class ConstantNode extends AbstractValueNode implements ITokenPattern
{
	const PATTERN = [
		Token::CONSTANT
	];
	
	public function __construct(mixed $value)
    {
        parent::__construct($value);
    }
	
	public static function getToken(): Token {
		return Token::CONSTANT;
	}
	
	public static function matches(array $tokens): bool {
		return NodeFactory::checkTokenPattern($tokens,self::PATTERN);
	}

    public static function fromString(string $expression): ?self
    {
        $expression = trim($expression);

        // Bool
        if (strcasecmp($expression, 'true') === 0) {
            return new self(true);
        }

        if (strcasecmp($expression, 'false') === 0) {
            return new self(false);
        }

        // Null
        if (strcasecmp($expression, 'null') === 0) {
            return new self(null);
        }

        // Numeric (int or float)
        if (is_numeric($expression)) {
            return new self(str_contains($expression, '.') ? (float)$expression : (int)$expression);
        }

        // Quoted string (single or double)
        if (
            (str_starts_with($expression, '"') && str_ends_with($expression, '"')) ||
            (str_starts_with($expression, "'") && str_ends_with($expression, "'"))
        ) {
            $value = substr($expression, 1, -1);
            // stripslashes nur wenn Escapes erwartet werden
            return new self(stripslashes($value));
        }

        return null;
    }

    public static function fromTokens(array $tokens,int &$i=0): ?self
    {
        if (count($tokens) === 1) {
            $token = $tokens[0];
            return match ($token[0]) {
                Token::STRING => new self($token[1]),
                Token::CONSTANT => new self((bool)$token[1]),
				Token::NUMBER => new self( 
					strpos($token[1],'.') > 0 ? 
						floatval($token[1]) : 
						intval($token[1]) 
				),
                Token::NULL => new self(null),
                default => null,
            };
        }
        return null;
    }

}
