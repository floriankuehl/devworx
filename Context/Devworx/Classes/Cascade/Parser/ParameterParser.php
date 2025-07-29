<?php

namespace Cascade\Parser;

use Cascade\Enums\Token;

class ParameterParser
{
    /**
     * Parsed einen Parameterstring wie:
     *  name: 'Alex', age: 42, address: {user.address}
     *
     * @param string $input
     * @return array
     */
    public static function parse(string $input): array
    {
        $result = [];
        $length = strlen($input);
        $offset = 0;

        while ($offset < $length) {
            // Skip whitespace
            while ($offset < $length && ctype_space($input[$offset])) {
                $offset++;
            }

            // Parse key
            $key = self::parseKey($input, $offset);
            self::skipWhitespace($input, $offset);

            // Expect colon
            if (!isset($input[$offset]) || $input[$offset] !== ':') {
                throw new \InvalidArgumentException("Syntax error: Expected ':' after key '{$key}'");
            }
            $offset++; // Skip colon

            self::skipWhitespace($input, $offset);

            // Parse value
            $value = self::parseValue($input, $offset);
            $result[$key] = $value;

            self::skipWhitespace($input, $offset);

            // Skip comma if exists
            if (isset($input[$offset]) && $input[$offset] === ',') {
                $offset++;
            }
        }

        return $result;
    }

    protected static function parseKey(string $input, int &$offset): string
    {
        $length = strlen($input);
        $start = $offset;
        while ($offset < $length && preg_match('/[a-zA-Z0-9_\-]/', $input[$offset])) {
            $offset++;
        }
        return trim(substr($input, $start, $offset - $start));
    }

    protected static function parseValue(string $input, int &$offset)
    {
        self::skipWhitespace($input, $offset);

        if (!isset($input[$offset])) {
            return null;
        }

        $char = $input[$offset];

        if ($char === '"' || $char === "'") {
            return self::parseQuotedString($input, $offset);
        }

        if ($char === Token::OPEN->value) {
			$expression = self::parseNestedCascade($input, $offset);
            return Tokenizer::tokenize($expression);
        }

        // Simple value (true, false, null, number, unquoted strings)
        $value = '';
        $length = strlen($input);
        while ($offset < $length && !in_array($input[$offset], [','])) {
            $value .= $input[$offset++];
        }

        return self::castValue(trim($value));
    }

    protected static function parseQuotedString(string $input, int &$offset): string
    {
        $quote = $input[$offset++];
        $length = strlen($input);
        $value = '';

        while ($offset < $length) {
            if ($input[$offset] === '\\') {
                $offset++;
                if ($offset < $length) {
                    $value .= $input[$offset++];
                }
                continue;
            }

            if ($input[$offset] === $quote) {
                $offset++;
                break;
            }

            $value .= $input[$offset++];
        }

        return $value;
    }

    protected static function parseNestedCascade(string $input, int &$offset): string
    {
        $depth = 0;
        $length = strlen($input);
        $start = $offset;
        while ($offset < $length) {
            if ($input[$offset] === '{') {
                $depth++;
            } elseif ($input[$offset] === '}') {
                $depth--;
                if ($depth === 0) {
                    $offset++;
                    break;
                }
            }
            $offset++;
        }

        return substr($input, $start, $offset - $start);
    }

    protected static function castValue(string $value)
    {
        if (strtolower($value) === 'true') return true;
        if (strtolower($value) === 'false') return false;
        if (strtolower($value) === 'null') return null;
        if (is_numeric($value)) return $value + 0;
        return $value;
    }

    protected static function skipWhitespace(string $input, int &$offset): void
    {
        $length = strlen($input);
        while ($offset < $length && ctype_space($input[$offset])) {
            $offset++;
        }
    }
	
	public static function splitArguments(array $tokens): array
	{
		$args = [];
		$current = [];
		$depth = 0;

		foreach ($tokens as $token) {
			[$type, $value] = $token;

			if ($type === Token::OPEN_PAREN) {
				$depth++;
				$current[] = $token;
			} elseif ($type === Token::CLOSE_PAREN) {
				$depth--;
				$current[] = $token;
			} elseif ($type === Token::COMMA && $depth === 0) {
				$args[] = $current;
				$current = [];
			} else {
				$current[] = $token;
			}
		}

		if (!empty($current)) {
			$args[] = $current;
		}

		return $args;
	}

	
	public static function parseParameterList(array $tokens): ?array
	{
		$args = [];
		$current = [];
		$depth = 0;

		foreach ($tokens as $token) {
			[$type] = $token;

			if ($type === Token::COMMA && $depth === 0) {
				self::parseSingleArgument($current, $args);
				$current = [];
				continue;
			}

			if (in_array($type, Tokenizer::OPENER, true)) {
				$depth++;
			} elseif (in_array($type, Tokenizer::CLOSER, true)) {
				$depth--;
			}

			$current[] = $token;
		}

		if (!empty($current)) {
			self::parseSingleArgument($current, $args);
		}

		return in_array(null, $args, true) ? null : $args;
	}

    public static function parseSingleArgument(array $tokens, array &$args): void
	{
		if (empty($tokens)) return;

		$depth = 0;
		$colonIndex = null;

		foreach ($tokens as $i => [$type]) {
			if (in_array($type, Tokenizer::OPENER, true)) {
				$depth++;
			} elseif (in_array($type, Tokenizer::CLOSER, true)) {
				$depth--;
			} elseif ($type === Token::COLON && $depth === 0) {
				$colonIndex = $i;
				break;
			}
		}

		if ($colonIndex !== null && $colonIndex > 0) {
			$keyToken = $tokens[$colonIndex - 1];
			$key = $keyToken[1]; // value
			$valueTokens = array_slice($tokens, $colonIndex + 1);

			$node = NodeFactory::fromTokens($valueTokens);
			if ($node) {
				$args[$key] = $node;
			}
		} else {
			$node = NodeFactory::fromTokens($tokens);
			if ($node) {
				$args[] = $node;
			}
		}
	}

	public static function parseObjectTokens(array $tokens, int &$pos): ?array
	{
		$result = [];
		$count = count($tokens);

		if ($tokens[$pos][0] !== Token::OPEN) {
			return null;
		}

		$pos++; // skip '{'

		while ($pos < $count) {
			// Ende?
			if ($tokens[$pos][0] === Token::CLOSE) {
				$pos++;
				break;
			}

			// Schlüssel lesen
			$keyToken = $tokens[$pos++];
			if (!in_array($keyToken[0], [Token::STRING, Token::NUMBER, Token::IDENTIFIER], true)) {
				return null;
			}
			$key = $keyToken[1];

			// Erwartet ':'
			if ($tokens[$pos][0] !== Token::COLON) {
				return null;
			}
			$pos++;

			// Wert lesen
			$valueToken = $tokens[$pos];

			if ($valueToken[0] === Token::OPEN) {
				// Rekursiv nested Object
				$nested = self::parseObjectTokens($tokens, $pos);
				if ($nested === null) return null;
				$result[$key] = [Token::OBJECT, $nested];
			} else {
				// Primitive oder Ausdruck parsen
				$valueNode = NodeFactory::fromTokens([$valueToken]);
				if ($valueNode === null) return null;
				$result[$key] = [Token::VALUE, $valueNode];
				$pos++;
			}

			// Optionales Komma
			if ($tokens[$pos][0] === Token::COMMA) {
				$pos++;
			}
		}

		return $result;
	}

	public static function parseAttributes(array $tokens, int $start): array
	{
		$attributes = [];
		$pos = $start;
		$length = count($tokens);

		while ($pos < $length) {
			$token = $tokens[$pos];

			if ($token[0] === Token::GT || $token[0] === Token::DIV) {
				break;
			}

			if ($token[0] === Token::IDENTIFIER) {
				$attrName = $token[1];
				$pos++;

				if ($pos < $length && $tokens[$pos][0] === Token::ASSIGN) {
					$pos++;

					if ($pos < $length) {
						$valToken = $tokens[$pos];

						switch ($valToken[0]) {
							case Token::STRING:
								if (
									str_contains($valToken[1], Token::OPEN->value) &&
									str_contains($valToken[1], Token::CLOSE->value)
								) {
									$attributes[$attrName] = ExpressionParser::parseMixed($valToken[1]);
								} else {
									$attributes[$attrName] = $valToken[1];
								}
								$pos++;
								break;

							case Token::OBJECT:
							case Token::ARRAY:
							case Token::EXPRESSION:
								$attributes[$attrName] = NodeFactory::fromTokens([$valToken]);
								$pos++;
								break;

							default:
								$attributes[$attrName] = $valToken[1];
								$pos++;
								break;
						}
					}
				} else {
					// Attribut ohne Wert wie `disabled`
					$attributes[$attrName] = true;
				}
			} else {
				$pos++;
			}
		}

		return [$attributes, $pos];
	}

}
