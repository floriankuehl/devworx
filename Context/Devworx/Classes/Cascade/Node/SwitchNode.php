<?php

namespace Cascade\Node;

use Cascade\Runtime\SystemFunctions;
use Cascade\Runtime\Context;
use Cascade\Parser\ParameterParser;
use Cascade\Parser\NodeFactory;
use Cascade\Enums\Token;

class SwitchNode extends AbstractNode
{
    protected AbstractNode $condition;
    protected array $cases = [];
    protected ?AbstractNode $default = null;

    public function __construct(AbstractNode $condition, array $cases = [], ?AbstractNode $default = null)
    {
        $this->condition = $condition;
        $this->cases = $cases;
        $this->default = $default;
    }
	
	public static function getToken(): Token {
		return Token::EXPRESSION;
	}

    public static function fromTokens(array $tokens, int &$i=0): ?self
    {
        $str = trim(join('', array_column($tokens, 'value')));

        // Beispiel: switch(condition: ..., cases: [...], default: ...)
        if (!str_starts_with($str, 'switch(') || !str_ends_with($str, ')')) {
            return null;
        }

        $inner = substr($str, 7, -1);
        $args = ParameterParser::parse($inner);

        if (!isset($args['condition']) || !isset($args['cases'])) {
            return null;
        }

        $conditionNode = NodeFactory::fromString($args['condition']);

        // Cases parsen
        $caseList = json_decode($args['cases'], true);
        $caseNodes = [];

        if (!is_array($caseList)) {
            throw new \RuntimeException("SwitchNode: 'cases' müssen ein gültiges JSON-Array sein.");
        }

        foreach ($caseList as $case) {
            if (!isset($case['when']) || !isset($case['then'])) {
                continue;
            }

            $whenNode = NodeFactory::fromString($case['when']);
            $thenNode = NodeFactory::fromString($case['then']);
            $caseNodes[] = ['when' => $whenNode, 'then' => $thenNode];
        }

        $defaultNode = isset($args['default']) ? NodeFactory::fromString($args['default']) : null;

        return new self($conditionNode, $caseNodes, $defaultNode);
    }
	
	public function evaluate(Context|array $context): mixed
	{
		$conditionValue = $this->condition->evaluate($context);

		foreach ($this->cases as $case) {
			$whenValue = $case['when']->evaluate($context);

			if ($conditionValue == $whenValue) {
				return $case['then']->evaluate($context);
			}
		}

		if ($this->default !== null) {
			return $this->default->evaluate($context);
		}

		return null; // Fallback, wenn keine Bedingung zutrifft und kein default existiert
	}

    public function compile(string $contextVar = '$context', string $input='null'): string
    {
        $conditionCode = $this->condition->compile($contextVar);
        $code = "match ($conditionCode) {\n";

        foreach ($this->cases as $case) {
            $whenCode = $case['when']->compile($contextVar);
            $thenCode = $case['then']->compile($contextVar);
            $code .= "    $whenCode => $thenCode,\n";
        }

        if ($this->default) {
            $defaultCode = $this->default->compile($contextVar);
            $code .= "    default => $defaultCode,\n";
        }

        $code .= "}";
        return $code;
    }
}
