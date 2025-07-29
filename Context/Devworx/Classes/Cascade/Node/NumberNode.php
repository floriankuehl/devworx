<?php

namespace Cascade\Node;

use Cascade\Parser\NodeFactory;
use Cascade\Interfaces\ITokenPattern;
use Cascade\Runtime\Context;
use Cascade\Enums\Token;

class NumberNode extends AbstractValueNode implements ITokenPattern
{
	public const PATTERN = [
		Token::NUMBER
	];
	
	public function __construct(float|int $value)
    {
        parent::__construct($value);
    }

	public static function getToken(): Token {
		return Token::NUMBER;
	}
	
	public static function matches(array $tokens): bool {
		return NodeFactory::checkTokenPattern($tokens,self::PATTERN);
	}

    public static function fromString(string $expression): ?self
    {
        $trimmed = trim($expression);
        if (is_numeric($trimmed)) {
            $value = strpos($trimmed, '.') !== false ? (float)$trimmed : (int)$trimmed;
            return new self($value);
        }

        return null;
    }

    public static function fromTokens(array $tokens, int &$i=0): ?self
    {
		$value = $tokens[0][1] ?? null;
		if( $value === null )
			return $value;
		return new self( strpos($value, '.') !== false ? (float)$value : (int)$value );
    }

    public function compile(string $contextVar = '$context',string $input='null'): string
    {
        if (is_float($this->value)) {
            return rtrim(rtrim((string)$this->value, '0'), '.');
        }

        return (string)$this->value;
    }
	
}
