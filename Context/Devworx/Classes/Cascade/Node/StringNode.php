<?php

namespace Cascade\Node;

use Cascade\Parser\NodeFactory;
use Cascade\Interfaces\ITokenPattern;
use Cascade\Enums\Token;
use Cascade\Runtime\Context;

class StringNode extends AbstractValueNode implements ITokenPattern
{
	public const PATTERN = [
		Token::STRING
	];
	
    public function __construct(string $value)
    {
		parent::__construct($value);
    }
	
	public static function matches(array $tokens): bool {
		return NodeFactory::checkTokenPattern($tokens,self::PATTERN);
	}
	
	public static function getToken(): Token {
		return Token::STRING;
	}

    public static function fromString(string $expression): ?self
    {
        $trimmed = trim($expression);
        if (
            (str_starts_with($trimmed, '"') && str_ends_with($trimmed, '"')) ||
            (str_starts_with($trimmed, "'") && str_ends_with($trimmed, "'"))
        ) {
            return new self(stripcslashes(substr($trimmed, 1, -1)));
        }

        return null;
    }

    public static function fromTokens(array $tokens, int &$i=0): ?self
    {
		$token = $tokens[0] ?? null;
		if( $token === null ) 
			return $token;
		if( is_array($token) )
			return new self($tokens[0][1] ?? null);
		return new self($token);
    }

    public function compile(string $contextVar = '$context',string $input='null'): string
    {
		// String korrekt quoten (z. B. mit addslashes, alternativ var_export)
		return var_export($this->value . ($input === 'null' ? '' : $input),true);
    }
	
	public function evaluate(Context|array &$context,mixed $input=null): mixed
    {
		if( $input === null )
			return $this->value;
        return $this->value . $input;
    }
}
