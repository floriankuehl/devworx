<?php

namespace Cascade\Parser;

use Cascade\Enums\Token;

class Tokenizer {
	
	public const CONSTANTS = [
		'true', 'false', 'null'
	];
	
	public const SYMBOL_OPERATORS = [
		'!', 
		'==', '!=', '<', '<=', '>', '>=', '<>',
		'^', '!^', '^=', // xor, xnor, xor assign
		'|', '||', '!|', '|=', // bitor or, nor, or assign
		'&', '&&', '!&', '&=', // bitand, and, nand, and assign
		
		'+', '++', '+=',
		'-', '--', '-=',
		'*', '**', '*=',
		'/', '%', '/=',
	];
	
	public const OPENER = [Token::OPEN, Token::OPEN_PAREN];
	public const CLOSER = [Token::CLOSE, Token::CLOSE_PAREN];

	public const VALUES = [
		Token::CONSTANT,
		Token::STRING,
		Token::NUMBER,
		Token::OBJECT,
		Token::ARRAY,
		Token::EXPRESSION
	];
	
	public const UNARY_OPERATORS = [
		Token::NOT,
		Token::PLUS,
		Token::INCREMENT,
		Token::MINUS,
		Token::DECREMENT,
	];
	
	public const BINARY_OPERATORS = [
		Token::EQ,
		Token::ASSIGN,
		Token::NEQ,
		Token::LT, 
		Token::LTE,
		Token::GT, 
		Token::GTE,
		Token::LGT,
		
		Token::PLUS, 
		Token::MINUS, 
		Token::MULT,
		Token::DIV,
		Token::EXP, 
		Token::MOD, 
		
		Token::MINUS_ASSIGN,
		Token::PLUS_ASSIGN,
		Token::MULT_ASSIGN,
		Token::DIV_ASSIGN,
		
		Token::COALESCE,
		Token::BITWISE_AND,
		Token::AND, 
		Token::NAND,
		Token::AND_ASSIGN,
		Token::BITWISE_OR,
		Token::OR, 
		Token::NOR,
		Token::OR_ASSIGN,
		Token::XOR, 
		Token::XNOR,
		Token::XOR_ASSIGN,
		
		Token::SHIFT_RIGHT,
		Token::SHIFT_LEFT,
	];
	
	public const TERNARY_OPERATORS = [
		Token::TERNARY,
		Token::COLON,
	];
	
	public const SYNTAX = [
		' ' => Token::WHITESPACE,
		'_' => Token::IDENTIFIER,
		'.' => Token::DOT,
		':' => Token::COLON,
		',' => Token::COMMA,
		'(' => Token::OPEN_PAREN,
		')' => Token::CLOSE_PAREN,
		'"' => Token::STRING,
		"'" => Token::STRING,
		'{' => Token::OPEN,
		'}' => Token::CLOSE
	];
	
	public const STOP_TOKENS = [
		Token::PLUS,
		Token::MINUS,
		Token::MULT,
		Token::DIV,
		Token::COMMA,
		Token::CLOSE_PAREN,
		Token::AND,
		Token::OR,
		Token::EQ,
		Token::ASSIGN,
		Token::NEQ,
		Token::LT,
		Token::GT,
		Token::LTE,
		Token::GTE,
		Token::TERNARY,
		Token::COLON
	];
	
	public const PRECEDENCE = [
		Token::TERNARY->value => 0,
		// 1 - Logisches ODER
		Token::OR->value => 1, Token::NOR->value => 1,

		// 2 - Logisches UND und XOR
		Token::AND->value => 2, Token::NAND->value => 2,
		Token::XOR->value => 2, Token::XNOR->value => 2,

		// 3 - Vergleichsoperatoren
		Token::EQ->value => 3, Token::NEQ->value => 3, 
		Token::LT->value => 3, Token::LTE->value => 3, 
		Token::GT->value => 3, Token::GTE->value => 3,
		Token::LGT->value => 3, 

		// 4 - Addition/Subtraktion
		Token::PLUS->value  => 4, Token::MINUS->value  => 4,

		// 5 - Multiplikation etc.
		Token::MULT->value  => 5, Token::EXP->value => 5, 
		Token::DIV->value => 5, Token::MOD->value => 5,
		Token::SHIFT_LEFT->value => 5, Token::SHIFT_RIGHT->value => 5,
		
		//Bitwise
		Token::BITWISE_AND->value => 6, Token::BITWISE_OR->value => 6,

		// 6 - Zuweisung
		Token::ASSIGN->value  => 7, 
		Token::PLUS_ASSIGN->value => 7, Token::MINUS_ASSIGN->value => 7, 
		Token::MULT_ASSIGN->value => 7, Token::DIV_ASSIGN->value => 7, 
		Token::AND_ASSIGN->value => 7, Token::OR_ASSIGN->value => 7, 
		Token::XOR_ASSIGN->value => 7,

		// 7 - Null-Koaleszenz
		Token::COALESCE->value => 8,

		// 8 - Negation, Inversion, Inkrement
		Token::NOT->value => 9, Token::INCREMENT->value => 9, Token::DECREMENT->value => 9,
	];	
		
	public static function isUnaryOperator(Token $value): bool {
		return in_array($value,self::UNARY_OPERATORS,true);
	}
	
	public static function isBinaryOperator(Token $value): bool {
		return in_array($value,self::BINARY_OPERATORS,true);
	}
	
	public static function isTernaryOperator(Token $value): bool {
		return in_array($value,self::TERNARY_OPERATORS,true);
	}
	
	public static function isOperator(Token $value): bool {
		return self::isUnaryOperator($value) || 
			self::isBinaryOperator($value) || 
			self::isTernaryOperator($value);
	}
	
	public static function isSyntax(string $value): bool {
		return array_key_exists($value,self::SYNTAX);
	}
	
	public static function tokenize(string $input): array
	{
		$input = trim($input);
		$position = 0;
		$tokens = [];

		if (str_starts_with($input, Token::OPEN->value) && str_ends_with($input, Token::CLOSE->value)) {
			$input = substr($input, 1, -1);
		}

		while (!self::isEOF($input, $position)) {
			$char = self::peek($input, $position);

			
			if (ctype_space($char)) {
				$start = $position;
				while (!self::isEOF($input, $position) && ctype_space(self::peek($input, $position))) {
					self::consume($position);
				}
				$tokens[] = [Token::WHITESPACE, substr($input, $start, $position - $start)];
				continue;
			}

			$next = self::peek($input, $position+1);

			$consume = 0;
			$type = null;
			$value = null;

			$single = Token::tryFrom($char);
			$combined = Token::tryFrom($char . $next);

			if( self::isSyntax($char) ){
				$type = self::SYNTAX[$char];
				$value = $char;
				
				switch( $type ){
					case Token::STRING:
						$value = self::readQuotedString($input, $position, $char);
						break;
					case Token::IDENTIFIER:
						$value = self::readIdentifier($input, $position);
						break;
					case Token::OPEN:
						[$type, $value] = self::parseObjectOrArray($input, $position);
						break;
					default:
						$consume = 1;
						break;
				}
			} elseif( $combined ){
				$type = $combined;
				$value = $char.$next;
				$consume = 2;
			} elseif( $single ){
				$type = $single;
				$value = $char;
				$consume = 1;
			} elseif (ctype_alpha($char)) {
				$value = self::readIdentifier($input, $position);
				$type = match (true) {
					in_array(strtolower($value), self::CONSTANTS) => Token::CONSTANT,
					default => Token::IDENTIFIER,
				};
			} elseif (is_numeric($char)) {
				$type = Token::NUMBER;
				$value = self::readNumber($input,$position);
			}
			
			if ($type === null){
				throw new \RuntimeException("Unexpected character '{$char}' at position {$position}");
			}
			
			$tokens[] = [$type, $value];
			self::consume($position, $consume);
		}

		//echo \Devworx\Utility\DebugUtility::var_dump($tokens,'Tokenzing');

		return $tokens;
	}

	// --- Helper parsing logic ---

	protected static function parseObjectOrArray(string $input, int &$position): array
	{
		$subExpr = self::readBracedExpression($input, $position);
		$tokens = self::tokenize($subExpr);
		
		if( count($tokens) === 1 ) 
			return $tokens[0];

		$keyTypes = [];
		for ($i = 0; $i < count($tokens) - 2; $i++) {
			[$type1] = $tokens[$i];
			[$type2] = $tokens[$i + 1];
			if ($type2 === Token::COLON) {
				$keyTypes[] = $type1;
			}
		}

		$isAllNumeric = !empty($keyTypes) && count(array_filter($keyTypes, fn($t) => $t !== Token::NUMBER)) === 0;
		return [$isAllNumeric ? Token::ARRAY : Token::OBJECT, $tokens];
	}

	protected static function readIdentifier(string $input, int &$position): string
	{
		$start = $position;
		while (!self::isEOF($input, $position) && preg_match('/[a-zA-Z0-9_]/', self::peek($input, $position))) {
			self::consume($position);
		}
		return substr($input, $start, $position - $start);
	}

	protected static function readNumber(string $input, int &$position): string
	{
		$start = $position;
		while (!self::isEOF($input, $position) && preg_match('/[\d\.\-]/', self::peek($input, $position))) {
			self::consume($position);
		}
		return substr($input, $start, $position - $start);
	}

	protected static function readQuotedString(string $input, int &$position, string $quote): string
	{
		self::consume($position); // skip opening quote
		$start = $position;

		while (!self::isEOF($input, $position) && self::peek($input, $position) !== $quote) {
			if (self::peek($input, $position) === '\\') {
				self::consume($position, 2);
			} else {
				self::consume($position);
			}
		}

		$string = substr($input, $start, $position - $start);
		self::consume($position); // closing quote
		return stripcslashes($string);
	}

	protected static function readBracedExpression(string $input, int &$position): string
	{
		$start = $position;
		$depth = 0;
		$inString = false;
		$stringChar = null;
		$escaped = false;

		while (!self::isEOF($input, $position)) {
			$char = self::peek($input, $position);

			if ($escaped) {
				$escaped = false;
			} else {
				match (true) {
					($char === '\\') => $escaped = true,
					($inString && $char === $stringChar) => [$inString, $stringChar] = [false, null],
					(!$inString && ($char === '"' || $char === "'")) => [$inString, $stringChar] = [true, $char],
					(!$inString && $char === Token::OPEN->value) => $depth++,
					(!$inString && $char === Token::CLOSE->value) => $depth--,
					default => null
				};
			}

			self::consume($position);
			if ($depth === 0 && !$inString) break;
		}

		return substr($input, $start, $position - $start);
	}

	// --- Utility methods ---

	protected static function peek(string $input, int $position): ?string
	{
		return $input[$position] ?? null;
	}

	protected static function consume(int &$position, int $count = 1): void
	{
		$position += $count;
	}

	protected static function isEOF(string $input, int $position): bool
	{
		return $position >= strlen($input);
	}

	// --- Convenience functions ---

	public static function token($type, $value = null): array
	{
		return [$type, $value];
	}

	public static function untoken(array $data, &$type = null, &$value = null): bool
	{
		if (empty($data)) return false;
		[$type, $value] = $data;
		return true;
	}

	public static function parsePipe(array $tokens): ?AbstractNode
	{
		$parts = self::splitByPipe($tokens);
		if (count($parts) < 2) return null;

		$node = NodeFactory::fromTokens($parts[0]);
		for ($i = 1; $i < count($parts); $i++) {
			$right = NodeFactory::fromTokens($parts[$i]);
			if (!$right) return null;
			$node = new PipeNode($node, $right);
		}

		return $node;
	}

	public static function splitByPipe(array $tokens): array
	{
		$groups = [];
		$current = [];
		$depth = 0;

		foreach ($tokens as $token) {
			if (in_array($token[0], self::OPENER)) $depth++;
			if (in_array($token[0], self::CLOSER)) $depth--;

			if ($token[0] === Token::PIPE && $depth === 0) {
				$groups[] = $current;
				$current = [];
			} else {
				$current[] = $token;
			}
		}

		if (!empty($current)) {
			$groups[] = $current;
		}

		return $groups;
	}
}