<?php

namespace Cascade\Node;

use Cascade\Runtime\Context;
use Cascade\Enums\Token;

class TernaryNode extends AbstractNode
{
    public function __construct(
        protected AbstractNode $condition,
        protected AbstractNode $ifTrue,
        protected AbstractNode $ifFalse
    ) {}
	
	public static function getToken(): Token {
		return Token::EXPRESSION;
	}

    public function evaluate(Context|array &$context, mixed $input = null): mixed
    {
        $cond = $this->condition->evaluate($context, $input);
        return $cond ? $this->ifTrue->evaluate($context, $input) : $this->ifFalse->evaluate($context, $input);
    }

    public function compile(string $contextVar = '$context', string $input = 'null'): string
    {
        $c = $this->condition->compile($contextVar, $input);
        $t = $this->ifTrue->compile($contextVar, $input);
        $f = $this->ifFalse->compile($contextVar, $input);

        return "({$c} ? {$t} : {$f})";
    }
}
