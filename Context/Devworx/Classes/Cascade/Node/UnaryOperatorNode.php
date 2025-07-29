<?php

namespace Cascade\Node;

use Cascade\Runtime\Context;
use Cascade\Enums\Token;

class UnaryOperatorNode extends AbstractNode
{
    public function __construct(
        protected Token $operator,
        protected AbstractNode $operand
    ) {}

	public static function getToken(): Token {
		return Token::EXPRESSION;
	}

    public function evaluate(Context|array &$context, mixed $input = null): mixed
    {
        $value = $this->operand->evaluate($context, $input);

		//bool int and float check?
        return match ($this->operator) {
            Token::NOT => !$value,
            Token::MINUS => -$value,
			Token::DECREMENT => --$value,
            Token::PLUS => +$value,
			Token::INCREMENT => ++$value,
            default => throw new \RuntimeException("Unknown unary operator '{$this->operator->value}'"),
        };
    }

    public function compile(string $contextVar = '$context', string $input = 'null'): string
    {
        $compiled = $this->operand->compile($contextVar, $input);
        return "({$this->operator->value}{$compiled})";
    }
}
