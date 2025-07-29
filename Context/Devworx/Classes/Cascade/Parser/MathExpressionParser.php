
<?php

namespace Cascade\Parser;

use Cascade\Node\MathExpressionNode;

use Cascade\Parser\InlineExpressionTokenizer;

use Cascade\Node\ConstantNode;
use Cascade\Node\AddNode;
use Cascade\Node\SubtractNode;
use Cascade\Node\MultiplyNode;
use Cascade\Node\DivideNode;
use Cascade\Node\ModNode;

class MathExpressionParser
{
	const OP_MAP = [
		'PLUS'  => ['+', 1],
		'MINUS' => ['-', 1],
		'MULT'  => ['*', 2],
		'DIV'   => ['/', 2],
		'MOD'   => ['%', 2],
	];
	
    protected array $tokens;
    protected int $pos = 0;

    public function __construct(array $tokens) {
        $this->tokens = $tokens;
    }

    public function parse(): MathExpressionNode {
        return $this->parseExpression(0);
    }

    protected function parseExpression(int $minPrecedence): MathExpressionNode {
        $node = $this->parsePrimary();

        while ($this->matchOperator($minPrecedence, $op, $precedence)) {
            $this->next(); // skip operator
            $right = $this->parseExpression($precedence + 1);
            $node = $this->createBinaryNode($op, $node, $right);
        }

        return $node;
    }

    protected function parsePrimary(): MathExpressionNode {
        $token = $this->peek();

        if ($token['type'] === NodeType::NUMBER) {
            $this->next();
            return new ConstantNode($token['value']);
        }

        if ($token['type'] === NodeType::PAREN_OPEN) {
            $this->next();
            $expr = $this->parseExpression(0);
            $this->expect( NodeType::PAREN_CLOSE );
            return $expr;
        }

        if ( $token['type'] === NodeType::IDENTIFIER ) {
            // z. B. user.age oder VariableAccessNode
            return VariableAccessNode::fromTokens($this->tokens, $this->pos);
        }

        throw new Exception("Unerwartetes Token: " . json_encode($token));
    }

    protected function matchOperator(int $minPrec, ?string &$op = null, ?int &$prec = null): bool {
        $token = $this->peek();

        if ($token && isset( self::OP_MAP[$token['type']])) {
            [$operator, $precedence] = self::OP_MAP[$token['type']];
            if ($precedence >= $minPrec) {
                $op = $operator;
                $prec = $precedence;
                return true;
            }
        }

        return false;
    }

    protected function createBinaryNode(string $op, MathExpressionNode $left, MathExpressionNode $right): MathExpressionNode {
        return match($op) {
            '+' => new AddNode($left, $right),
            '-' => new SubtractNode($left, $right),
            '*' => new MultiplyNode($left, $right),
            '/' => new DivideNode($left, $right),
            '%' => new ModNode($left, $right),
        };
    }

    protected function peek(): ?array {
        return $this->tokens[$this->pos] ?? null;
    }

    protected function next(): void {
        $this->pos++;
    }

    protected function expect(string $type): void {
        if ($this->tokens[$this->pos]['type'] !== $type) {
            throw new Exception("Erwartet Token $type, aber gefunden: " . $this->tokens[$this->pos]['type']);
        }
        $this->next();
    }
}
