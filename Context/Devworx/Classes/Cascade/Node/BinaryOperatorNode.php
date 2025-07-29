<?php

namespace Cascade\Node;

use Cascade\Runtime\Context;

use Cascade\Enums\Token;

class BinaryOperatorNode extends AbstractNode
{
    public function __construct(
        protected AbstractNode $left,
		protected Token $operator,
        protected AbstractNode $right
    ) {}
	
	public static function getToken(): Token {
		return Token::EXPRESSION;
	}
	
	public function setLeft(AbstractNode $node): void {
		$this->left = $node;
	}
	
	public function setRight(AbstractNode $node): void {
		$this->right = $node;
	}
	
	public function setOperator(Token $operator): void {
		$this->operator = $operator;
	}

    public function evaluate(Context|array &$context, mixed $input = null): mixed
    {
        $a = $this->left->evaluate($context);
        $b = $this->right->evaluate($context);

        return match ($this->operator) {
            Token::PLUS => $a + $b,
			Token::PLUS_ASSIGN => $a += $b,
            Token::MINUS => $a - $b,
			Token::MINUS_ASSIGN => $a -= $b,
            Token::MULT => $a * $b,
			Token::MULT_ASSIGN => $a *= $b,
			Token::EXP => $a ** $b,
            Token::DIV => $a / $b,
			Token::DIV_ASSIGN => $a /= $b,
            Token::MOD => $a % $b,
			Token::ASSIGN => $a = $b,
            Token::EQ => $a == $b,
            Token::NEQ => $a != $b,
            Token::LT => $a < $b,
            Token::LTE => $a <= $b,
            Token::GT => $a > $b,
            Token::GTE => $a >= $b,
			Token::LGT => ( $a < $b || $a > $b ),
            Token::AND => $a && $b,
			Token::NAND => !($a && $b),
            Token::OR => $a || $b,
			Token::NOR => !($a || $b),
			Token::BITWISE_AND => $a & $b,
			Token::BITWISE_OR => $a | $b,
			Token::XOR => $a xor $b,
			Token::XNOR => !($a xor $b),
			Token::SHIFT_RIGHT => $a >> $b,
			Token::SHIFT_LEFT => $a << $b,
			Token::COALESCE => $a ?? $b,
            default => throw new \RuntimeException("Unknown operator {$this->operator->name}"),
        };
    }

    public function compile(string $contextVar = '$context', string $input = 'null'): string
    {
        $a = $this->left->compile($contextVar);
        $b = $this->right->compile($contextVar);
		
		//TODO: CUSTOM OPERATORS LIKE !^ !&
        return "($a {$this->operator->value} $b)";
    }
	
	/*
	public function evaluate(Context|array $context, mixed $input = null): mixed
    {
        $a = $this->left->evaluate($context);
        $b = $this->right->evaluate($context);

        return match ($this->operator) {
            '+' => $a + $b,
			'+=' => $a += $b,
            '-' => $a - $b,
			'-=' => $a -= $b,
            '*' => $a * $b,
			'*=' => $a *= $b,
			'**' => $a ** $b,
            '/' => $a / $b,
			'/=' => $a /= $b,
            '%' => $a % $b,
			'=' => $a = $b,
            '==' => $a == $b,
            '!=' => $a != $b,
            '<' => $a < $b,
            '<=' => $a <= $b,
            '>' => $a > $b,
            '>=' => $a >= $b,
			'<>' => ( $a < $b || $a > $b ),
            '&&' => $a && $b,
			'!&' => !($a && $b),
            '||' => $a || $b,
			'!|' => !($a || $b),
			'&' => $a & $b,
			'|' => $a | $b,
			'^' => $a xor $b,
			'!^' => !($a xor $b),
			'>>' => $a >> $b,
			'<<' => $a << $b,
            default => throw new \RuntimeException("Unknown operator {$this->operator}"),
        };
    }
	*/
}
