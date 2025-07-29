<?php

namespace Cascade\Node;

use Cascade\Enums\Token;
use Cascade\Interfaces\ITokenPattern;
use Cascade\Parser\Tokenizer;
use Cascade\Parser\NodeFactory;
use Cascade\Runtime\Context;

class VariableNode extends AbstractNode implements ITokenPattern
{
	public const PATTERN = [
		Token::IDENTIFIER
	];
	
    /** @var string[] */
    protected array $path;

    /**
     * Konstruktor.
     * 
     * @param string[] $path Pfad der Variable, z.B. ['users', '0', 'name']
     */
    public function __construct(array $path)
    {
        $this->path = $path;
    }

	public static function getToken(): Token {
		return Token::VARIABLE;
	}

	public static function matches(array $tokens): bool {
		return NodeFactory::checkTokenPattern($tokens,self::PATTERN);
	}

	/**
     * Generiert eine node aus tokens
     *
     * @return ?AbstractNode
     */
	public static function fromTokens(array $tokens, int &$i=0): ?AbstractNode
	{
		$parts = [];

		for ($i = 0; $i < count($tokens); $i += 2) {
			Tokenizer::untoken($tokens[$i],$type,$value);
			$parts[] = $value;

			if ($i + 1 < count($tokens)) {
				$dot = $tokens[$i + 1];
				if ($dot[0] !== Token::DOT) {
					return null;
				}
			}
		}

		return new self($parts);
	}


    /**
     * Kompiliert die VariableAccessNode zu PHP-Code, der die Variable aus dem Kontext holt.
     * Beispiel-Ausgabe: `$context->get("users.0.name")`
     *
     * @return string
     */
    public function compile(string $contextVar='$context',string $input='null'): string
    {
        // Erzeugt z. B.: $context->get("foo.bar.baz")
		$path = implode('.', array_map('addslashes', $this->path));
		return $contextVar . '->get("' . $path . '")';
    }
	
	/**
     * Liest aus array oder Context
     *
     * @return mixed
     */
    public function evaluate(Context|array &$context,mixed $input=null): mixed
    {
		if( is_array($context) ){
			$tmp = $context;
			foreach( $this->path as $path ){
				if( isset($tmp[$path]) ){
					$tmp = $tmp[$path];
					continue;
				}
				return null;
			}
		}
		return $context->get( implode('.',$this->path) );
    }
}
