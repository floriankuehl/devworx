<?php

namespace Cascade\Node;

use Cascade\Enums\Token;
use Cascade\Interfaces\INode;
use Cascade\Parser\NodeFactory;
use Cascade\Parser\ParameterParser;
use Cascade\Runtime\ViewHelperInvoker;
use Cascade\Runtime\Context;
use Cascade\Interfaces\ITokenPattern;

class ViewHelperNode extends AbstractNode implements ITokenPattern
{
	public const PATTERN = [
		Token::IDENTIFIER, // namespace
		Token::COLON,
		Token::IDENTIFIER, // name
		NodeFactory::PATTERN_OPTIONAL,
		[ Token::DOT, Token::IDENTIFIER ],
		Token::OPEN_PAREN,
	];
	
    protected string $helperName;
    protected array $arguments;

    public function __construct(string $helperName, array $arguments)
    {
        $this->helperName = $helperName;
        $this->arguments = $arguments;
    }

	public static function getToken(): Token {
		return Token::VIEWHELPER;
	}

	public static function matches(array $tokens): bool {
		return NodeFactory::checkTokenPattern($tokens,self::PATTERN);
	}

    public static function fromTokens(array $tokens, int &$i=0): ?self
	{
		// Namespace ist erstes Token value
		$namespace = $tokens[0][1]; // $tokens[0] = [$type, $value]
		
		// Der zweite Token muss COLON sein
		if ($tokens[1][0] !== Token::COLON) {
			return null; // Sicherheitshalber, sollte vom Pattern gedeckt sein
		}

		// Helfername kann aus mehreren IDENTIFIER + DOT Tokens bestehen, ab Index 2
		$helperNameParts = [];
		$pos = 2;
		while (isset($tokens[$pos]) && ($tokens[$pos][0] === Token::IDENTIFIER || $tokens[$pos][0] === Token::DOT)) {
			if( $tokens[$pos][0] === Token::DOT ){
				$pos++;
				continue;
			}
			$helperNameParts[] = $tokens[$pos][1];
			$pos++;
		}
		$helperName = implode('.', $helperNameParts);

		// Nun sollte das nächste Token OPEN_PAREN sein
		if (!isset($tokens[$pos]) || $tokens[$pos][0] !== Token::OPEN_PAREN) {
			return null;
		}

		// Suche das korrespondierende CLOSE_PAREN
		$argStart = $pos + 1;
		$argEnd = null;
		for ($i = $argStart; $i < count($tokens); $i++) {
			if ($tokens[$i][0] === Token::CLOSE_PAREN) {
				$argEnd = $i;
				break;
			}
		}
		if ($argEnd === null) {
			return null; // Keine schließende Klammer gefunden
		}

		$argTokens = array_slice($tokens, $argStart, $argEnd - $argStart);

		// Parameter parsen via ParameterParser
		$arguments = [];
		try {
			$arguments = ParameterParser::parseParameterList($argTokens);
		} catch (\InvalidArgumentException $e) {
			// Fehler beim Parsen – vielleicht null oder Fehlerbehandlung hier
			return null;
		}

		$fullHelperName = $namespace . ':' . $helperName;

		return new self($fullHelperName, $arguments);
	}


	public function evaluate(Context|array &$context,mixed $input=null): mixed
	{
		$evaluatedArgs = [];

		foreach ($this->arguments as $key => $argNode) {
			if ($argNode instanceof INode) {
				$evaluatedArgs[$key] = $argNode->evaluate($context);
			} else {
				// Falls Argument kein Node ist (z. B. beim vereinfachten Parsing), direkt übernehmen
				$evaluatedArgs[$key] = $argNode;
			}
		}
		
		return ViewHelperInvoker::invoke($context,$this->helperName, $evaluatedArgs, $input);
	}

    public function compile(string $contextVar = '$context', string $input = 'null'): string
	{
		$compiledArgs = [];
		$explicitValueSet = false;

		foreach ($this->arguments as $argName => $argNode) {
			if ($argName === 'value') {
				$explicitValueSet = true;
			}
			$compiledArgs[] = var_export($argName, true) . ' => ' . $argNode->compile($contextVar, 'null');
		}

		$compiledArgsString = '[' . implode(', ', $compiledArgs) . ']';
		$funcName = var_export($this->helperName, true);

		if ($input !== 'null' && !$explicitValueSet) {
			// Input wird nur übergeben, wenn kein value explizit gesetzt wurde
			return ViewHelperInvoker::class . "::invoke($contextVar, $funcName, $compiledArgsString, $input)";
		}

		// Entweder kein Input vorhanden oder value bereits gesetzt
		return ViewHelperInvoker::class . "::invoke($contextVar, $funcName, $compiledArgsString)";
	}

}
