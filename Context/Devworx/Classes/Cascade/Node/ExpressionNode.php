<?php

namespace Cascade\Node;

use Cascade\Runtime\Context;
use Cascade\Parser\ExpressionParser;
use Cascade\Enums\Token;
use Cascade\Interfaces\INode;

class ExpressionNode extends AbstractNode
{
		
    /**
     * @var AbstractNode[]
     */
    protected array $nodes;

    /**
     * @param AbstractNode[] $nodes
     */
    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }
	
	public static function getToken(): Token {
		return Token::EXPRESSION;
	}
	
	public static function getValueType(): string {
		return Token::EXPRESSION->value;
	}

    public static function fromString(string $expression): ?AbstractNode
    {
        return null; // nicht mehr zuständig
    }

	public static function fromTokens(array $tokens, int &$i=0): ?AbstractNode
	{
		return ExpressionParser::parse($tokens);
	}

    public function evaluate(Context|array &$context,mixed $input=null): mixed
    {
        $result = '';
        foreach ($this->nodes as $node) {
            $evaluated = $node->evaluate($context);
            $result .= is_scalar($evaluated) || $evaluated === null
                ? (string) $evaluated
                : json_encode($evaluated);
        }
        return $result;
    }

	public function compile(string $contextVar = '$context', string $input = null): string
	{
		$compiledParts = [];

		foreach ($this->nodes as $child) {
			$compiled = trim($child->compile($contextVar, $input));

			// Nur nicht-leere Strings hinzufügen
			if ($compiled !== "''" && $compiled !== '') {
				$compiledParts[] = $compiled;
			}
		}

		return implode(', ', $compiledParts);
	}



	protected function collectVariables(?INode $node=null): array
	{
		if( $node === null ) $node = $this;
		$result = [];

		if ($node instanceof VariableNode) {
			$result[] = $node->getPath(); // z. B. ['user', 'name']
		}

		foreach ($node->nodes as $child) {
			$result = array_merge($result, $this->collectVariables($child));
		}

		return $result;
	}

}
