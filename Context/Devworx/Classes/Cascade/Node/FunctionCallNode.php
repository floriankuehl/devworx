<?php

namespace Cascade\Node;

use Cascade\Enums\Token;
use Cascade\Parser\NodeFactory;
use Cascade\Parser\ParameterParser;
use Cascade\Parser\ExpressionParser;
use Cascade\Runtime\SystemFunctions;
use Cascade\Runtime\Functions;
use Cascade\Runtime\Context;
use Cascade\Interfaces\ITokenPattern;

class FunctionCallNode extends AbstractNode implements ITokenPattern
{
    public const PATTERN = [
        Token::IDENTIFIER,
        Token::OPEN_PAREN,
    ];

    protected string $functionName;

    /** @var array<int|string, AbstractNode> */
    protected array $arguments;

    protected static array $functionAliases = [
        'upper'  => 'strtoupper',
        'lower'  => 'strtolower',
        'json'   => 'json_encode',
        'unjson' => 'json_decode',
        'split'  => 'str_split',
        'join'   => 'implode',
		'random' => 'rand',
		'list' => 'is_array'
    ];

    protected static array $allowedFunctions = [
        'pow', 'sqrt', 'abs', 'round', 'ceil', 'floor',
        'strtoupper', 'strtolower', 'str_split',
		'min', 'max',
        'json_encode', 'json_decode',
        'nl2br', 'explode', 'implode',
		'cos', 'sin', 'tan', 'atan', 'atan2','atanh',
		'rand', 'count', 'first', 'end', 'is_array'
    ];

    protected static array $systemFunctions = ['if', 'in'];

    public function __construct(string $functionName, array $arguments = [])
    {
        $this->functionName = $functionName;
        $this->arguments = $arguments;
    }
	
	public static function getToken(): Token {
		return Token::FUNCTION;
	}

    public static function matches(array $tokens): bool
    {
        return NodeFactory::checkTokenPattern($tokens,self::PATTERN);
    }

    public static function fromTokens(array $tokens, int &$i = 0): ?AbstractNode
	{
		// Funktionsaufrufe brauchen mindestens 3 Tokens: name ( identifier ), Klammern
		if (count($tokens) < 3) {
			return null;
		}

		$first = $tokens[0][1] ?? null;
		$second = $tokens[1][0] ?? null;
		$last = end($tokens)[0] ?? null;

		// Gültigkeit prüfen: z. B. abs(...) oder sin(...)
		if (!is_string($first) || $second !== Token::OPEN_PAREN || $last !== Token::CLOSE_PAREN) {
			return null;
		}

		// Extrahiere nur den Teil in den Klammern
		$innerTokens = array_slice($tokens, 2, -1);

		// Nutze splitArguments(), um verschachtelte Argumente sauber zu trennen
		$argTokenGroups = ParameterParser::splitArguments($innerTokens);

		$arguments = [];

		foreach ($argTokenGroups as $argTokens) {
			$node = ExpressionParser::parse($argTokens);
			if (!$node) return null;
			$arguments[] = $node;
		}

		return new self($first, $arguments);
	}

    public function evaluate(Context|array &$context,mixed $input=null): mixed
    {
        $func = $this->resolveFunctionName($this->functionName);

        if (!in_array($func, self::$allowedFunctions) && !in_array($func, self::$systemFunctions)) {
            throw new \RuntimeException("Function '{$this->functionName}' is not allowed.");
        }

        $evaluatedArgs = [];
		if( isset($input) ) 
			$evaluatedArgs[] = $input;
		
		//echo \Devworx\Utility\DebugUtility::var_dump($this->arguments,$this->functionName,__METHOD__,__LINE__);
		
        foreach ($this->arguments as $key => $node) {
			//if( $node === null ) continue;
            $evaluatedArgs[] = $node->evaluate($context);
        }
				
		if (in_array($func, self::$systemFunctions)) {
            return SystemFunctions::call($func, $evaluatedArgs);
        }

        if (is_callable($func)) {
            return call_user_func_array($func, array_values($evaluatedArgs));
        }

        throw new \RuntimeException("Function '{$func}' is not callable.");
    }

    public function compile(string $contextVar = '$context', string $input = 'null'): string
	{
		$func = $this->resolveFunctionName($this->functionName);

		if (!in_array($func, self::$allowedFunctions) && !in_array($func, self::$systemFunctions)) {
			throw new \RuntimeException("Function '{$func}' is not allowed.");
		}

		$compiledArgs = [];
		$explicitValueSet = false;

		foreach ($this->arguments as $key => $node) {
			// Wenn benannter Parameter: nicht $input durchreichen
			if (is_string($key)) {
				$compiled = $node->compile($contextVar, 'null');
				if ($key === 'value') {
					$explicitValueSet = true;
				}
				$compiledArgs[] = "'$key' => $compiled";
			} else {
				// Unbenannter Parameter bekommt den Input
				$compiled = $node->compile($contextVar, $input);
				$compiledArgs[] = $compiled;
			}
		}
		
		$finalInput = 'null';
		if ($input !== 'null' && !$explicitValueSet) {
			// Nur hinzufügen, wenn 'value' nicht explizit gesetzt wurde
			$finalInput = $input;
			$compiledArgs = array_merge(["'value' => $input"], $compiledArgs);
		}

		return $this->compileFunctionCall($contextVar, '[' . implode(', ', $compiledArgs) . ']', $finalInput);
	}

    protected function resolveFunctionName(string $name): string
    {
        $name = strtolower($name);
        return self::$functionAliases[$name] ?? $name;
    }

    public static function addAlias(string $alias, string $functionName): void
    {
        self::$functionAliases[$alias] = $functionName;
    }
	
	protected function compileFunctionCall(string $contextVar, string $argsCode, string $input = 'null'): string
	{
		$func = $this->resolveFunctionName($this->functionName);
		$functionName = var_export($func, true);
		$isSystem = in_array($func, self::$systemFunctions);
		$targetClass = $isSystem ? SystemFunctions::class : Functions::class;

		// Wenn input === 'null' oder Funktion ist Systemfunktion → kein Input übergeben
		if ($input === 'null' || $isSystem) {
			return "{$targetClass}::call({$functionName}, {$argsCode})";
		}

		// Prüfen, ob 'value' im $argsCode schon vorkommt
		if (str_contains($argsCode, "'value' =>")) {
			return "{$targetClass}::call({$functionName}, {$argsCode})";
		}

		// Sonst input explizit anhängen
		return "{$targetClass}::call({$functionName}, {$argsCode}, {$input})";
	}



}
