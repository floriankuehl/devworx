<?php

namespace Cascade\Node;

use Cascade\Runtime\Context;
use Cascade\Parser\ParameterParser;
use Cascade\Parser\NodeFactory;
use Cascade\Interfaces\INode;
use Cascade\Interfaces\ITokenPattern;
use Cascade\Enums\Token;

use \Devworx\Utility\ArrayUtility;

class ObjectNode extends AbstractValueNode implements ITokenPattern
{
	public const PATTERN = [
		Token::IDENTIFIER,
		Token::COLON,
		Token::VALUE
	];
	
    /**
     * @var AbstractNode[]
     */
    protected array $keys = [];

    /**
     * @var AbstractNode[]
     */
    protected array $values = [];

    /**
     * @param AbstractNode[] $keys
     * @param AbstractNode[] $values
     */
    public function __construct(array $keys, array $values=null)
    {
		parent::__construct([]);
		
		if( $values === null ){
			$this->value = $keys;
			$this->keys = array_keys($keys);
			$this->values = array_values($keys);
			return;
		}
		
        $this->keys = $keys;
        $this->values = $values;
    }

	public static function getToken(): Token {
		return Token::OBJECT;
	}

	public static function matches(array $tokens): bool {
		return NodeFactory::checkTokenPattern($tokens,self::PATTERN);
	}

    public static function fromTokens(array $tokens, int &$i=0): ?self
    {
		$parameters = ParameterParser::parseParameterList($tokens);
        return new self( $parameters );
    }

    public function evaluate(Context|array &$context,mixed $input=null): mixed
    {
        $result = [];

        foreach ($this->keys as $i => $keyNode) {
			$valueNode = $this->values[$i];
            $key = $this->normalizeNode($keyNode)->evaluate($context);
            $value = $this->normalizeNode($valueNode)->evaluate($context);
            $result[$key] = $value;
        }
		
		if( $input === null )
			return $result;
		if( $input instanceof ObjectNode )
			$input = $input->evaluate($context);
		if( is_array($input) )
			return ArrayUtility::merge($result,$input);
		
		throw new \Exception("Invalid pipe input of type ".gettype($input)." for " . get_class($this));
		return $result;
    }

	public function normalizeNode(string|INode $node): INode {
		if( $node instanceof INode )
			return $node;
		if( is_string($node) )
			return new StringNode($node);
		return null;
	}

    public function compile(string $contextVar = '$context',string $input='null'): string
    {
        $compiled = [];
        foreach ($this->keys as $i => $keyNode) {
			$valueNode = $this->values[$i];
			$key = $this->normalizeNode($keyNode)->compile($contextVar);
			$value = $this->normalizeNode($valueNode)->compile($contextVar);
			
			//needs to be escaped?
			
			$compiled[] = "{$key} => {$value}";
        }
		$compiled = implode(',',$compiled);
		if( empty($input) || ( $input === 'null' ) )
			return "[{$compiled}]";
		
		if( ( str_starts_with($input,'[') && str_ends_with($input,']') ) )
			return ArrayUtility::class . "::merge([{$compiled}],{$input})";
		
		return "[{$compiled}]";
    }
	
	public static function getValueType(): string {
		return Token::OBJECT->value;
	}
}
