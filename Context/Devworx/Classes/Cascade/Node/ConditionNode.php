<?php

namespace Cascade\Node;

use Cascade\Interfaces\INode;
use Cascade\Parser\NodeFactory;
use Cascade\Parser\ExpressionEvaluator;
use Cascade\Enums\Token;
use Cascade\Interfaces\ITokenPattern;
use Cascade\Runtime\Context;

class ConditionNode extends AbstractNode implements ITokenPattern
{
    protected INode $conditionNode;
    protected INode $thenNode;
    protected ?INode $elseNode;

	public const PATTERN = [
		Token::IDENTIFIER,
		Token::OPEN_PAREN,
		Token::IDENTIFIER,
		Token::COLON,
		Token::VALUE,
		NodeFactory::PATTERN_OPTIONAL,
		[Token::COMMA,Token::IDENTIFIER,Token::COLON,Token::VALUE],
		Token::CLOSE_PAREN
	];

    public function __construct(INode $conditionNode, INode $thenNode, ?INode $elseNode = null)
    {
        $this->conditionNode = $conditionNode;
        $this->thenNode = $thenNode;
        $this->elseNode = $elseNode;
    }
	
	public static function matches(array $tokens): bool {
		return NodeFactory::checkTokenPattern($tokens,self::PATTERN);
	}
	
	public static function getToken(): Token {
		return Token::CONDITION;
	}

    public function evaluate(Context|array &$context,mixed $input=null): mixed
    {
		$conditionResult = $this->conditionNode->evaluate($context);
        // Stelle sicher, dass Ergebnis bool ist, oder zwinge Konvertierung
        $conditionBool = (bool)$conditionResult;

        if ($conditionBool) {
            return $this->thenNode->evaluate($context);
        }

        return $this->elseNode ? $this->elseNode->evaluate($context) : null;
    }

    public static function fromTokens(array $tokens, int &$i=0): ?self
	{
		$tokens = array_values($tokens);
		if (count($tokens) < 4) {
			return null;
		}

		// Muss mit "if" beginnen
		[$firstType, $firstVal] = $tokens[0];
		if (strtolower($firstVal) !== 'if') {
			return null;
		}

		$thenPos = null;
		$elsePos = null;

		foreach ($tokens as $i => [$type, $value]) {
			if ($type === Token::IDENTIFIER) {
				$lower = strtolower($value);
				if ($lower === 'then' && $thenPos === null) {
					$thenPos = $i;
				} elseif ($lower === 'else' && $elsePos === null) {
					$elsePos = $i;
				}
			}
		}

		if ($thenPos === null) {
			return null; // THEN ist Pflicht
		}

		$conditionTokens = array_slice($tokens, 1, $thenPos - 1);
		$thenTokens = ($elsePos !== null)
			? array_slice($tokens, $thenPos + 1, $elsePos - $thenPos - 1)
			: array_slice($tokens, $thenPos + 1);
		$elseTokens = ($elsePos !== null) ? array_slice($tokens, $elsePos + 1) : [];

		$conditionNode = NodeFactory::fromTokens($conditionTokens);
		$thenNode = NodeFactory::fromTokens($thenTokens);
		$elseNode = $elseTokens ? NodeFactory::fromTokens($elseTokens) : null;

		if (!$conditionNode || !$thenNode) {
			return null;
		}

		return new self($conditionNode, $thenNode, $elseNode);
	}


    public function compile(string $contextVar = '$context',string $input='null'): string
    {
        $conditionCode = $this->conditionNode->compile($contextVar);
        $thenCode = $this->thenNode->compile($contextVar);
        $elseCode = $this->elseNode ? $this->elseNode->compile($contextVar) : 'null';

        return "(($conditionCode) ? ($thenCode) : ($elseCode))";
    }
}
