<?php

namespace Cascade\Node;

use Cascade\Parser\ParameterParser;
use Cascade\Runtime\Context;
use Cascade\Parser\NodeFactory;
use Cascade\Interfaces\INode;
use Cascade\Interfaces\ITokenPattern;
use Cascade\Enums\Token;

class AssignmentNode extends AbstractNode implements ITokenPattern
{
    public const PATTERN = [
		Token::IDENTIFIER,
		Token::ASSIGN,
		Token::VALUE,
	];
	
	protected string $variableName;
    protected INode $valueNode;
	
	public static function matches(array $tokens): bool {
		return NodeFactory::checkTokenPattern($tokens,self::PATTERN);
	}
	
	public static function getToken(): Token {
		return Token::ASSIGN;
	}

    public function __construct(string $variableName, INode $valueNode)
    {
        $this->variableName = $variableName;
        $this->valueNode = $valueNode;
    }

    public function compile(string $contextVar='$context',string $input='null'): string
    {
        // Kompiliert zu $context->set('varName', <compiledValue>);
        $compiledValue = $this->valueNode->compile($contextVar,$input);
        return sprintf($contextVar.'->set(%s, %s);', var_export($this->variableName, true), $compiledValue);
    }

    public function evaluate(Context|array &$context,mixed $input=null): mixed
    {
        $value = $this->valueNode->evaluate($context, $input);
        if ($context instanceof Context) {
            $context->set($this->variableName, $value);
        } elseif (is_array($context)) {
            $context[$this->variableName] = $value;
        }
        return $value;
    }

    public static function fromTokens(array $tokens, int &$i=0): ?self
    {
		$path = [];
		while( isset($tokens[$i]) ){
			if( $tokens[$i][0] === Token::ASSIGN ){
				$i++;
				break;
			}
			if( $tokens[$i][0] === Token::DOT ){
				$i++;
				continue;
			}
			$path[] = $tokens[$i][1];
			$i++;
		}
		
		$tokens = array_slice($tokens,$i);
		
		return new self(
			implode('.',$path),
			NodeFactory::fromTokens($tokens)
		);
    }
}
