<?php

namespace Cascade\Parser;

use Cascade\Node\HTMLNode;
use Cascade\Node\StringNode;
use Cascade\Enums\Token;

class HTMLParser
{
    const SKIP_TAGS = ['style', 'script', 'devworx-debug'];

    public static function parse(array $tokens, int &$i = 0): array
    {
        $nodes = [];
        $length = count($tokens);

        while ($i < $length) {
            $token = $tokens[$i];

            // Behandlung von Kommentaren <!-- ... -->
            if (
                $token[0] === Token::LT &&
                isset($tokens[$i + 1], $tokens[$i + 2]) &&
                $tokens[$i + 1][0] === Token::MINUS &&
                $tokens[$i + 2][0] === Token::MINUS
            ) {
                $commentContent = self::consumeComment($tokens, $i);
                if ($commentContent !== null) {
                    $nodes[] = new StringNode('<!--' . $commentContent . '-->');
                    continue;
                }
            }

            // Behandlung von CDATA <![CDATA[ ... ]]>
            if (
                $token[0] === Token::LT &&
                isset($tokens[$i + 1], $tokens[$i + 2], $tokens[$i + 3], $tokens[$i + 4], $tokens[$i + 5], $tokens[$i + 6], $tokens[$i + 7]) &&
                $tokens[$i + 1][0] === Token::NOT && // '!'
                $tokens[$i + 2][1] === '[' &&
                $tokens[$i + 3][1] === 'C' &&
                $tokens[$i + 4][1] === 'D' &&
                $tokens[$i + 5][1] === 'A' &&
                $tokens[$i + 6][1] === 'T' &&
                $tokens[$i + 7][1] === 'A'
            ) {
                $cdataContent = self::consumeCDATA($tokens, $i);
                if ($cdataContent !== null) {
                    $nodes[] = new StringNode('<![CDATA[' . $cdataContent . ']]>');
                    continue;
                }
            }

            // Behandlung von <!DOCTYPE und generell <!
            if (
                $token[0] === Token::LT &&
                isset($tokens[$i + 1]) &&
                $tokens[$i + 1][0] === Token::NOT
            ) {
                // Wir lesen den Tag-Namen ab Position i+2 (nach <!)
                $pos = $i + 2;
                $tagName = self::readTagName($tokens, $pos);

                // Konsumiere den gesamten <! ... > Block als StringNode
                $rawContent = '';
                $depth = 0;
                $length = count($tokens);
                while ($i < $length) {
                    $rawContent .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                    if ($tokens[$i][0] === Token::GT && $depth === 0) {
                        $i++;
                        break;
                    }
                    if ($tokens[$i][0] === Token::LT) $depth++;
                    if ($tokens[$i][0] === Token::GT) $depth--;
                    $i++;
                }
                $nodes[] = new StringNode($rawContent);
                continue;
            }

            // Reguläres Tag (inkl. möglichkeit auf MINUS im TagName)
            if (
                $token[0] === Token::LT &&
                isset($tokens[$i + 1])
            ) {
                $pos = $i + 1;

                // Optional schließendes Tag </
                if ($tokens[$pos][0] === Token::DIV) {
                    $pos++;
                }

                $tagName = self::readTagName($tokens, $pos);

                if ($tagName !== null && in_array($tagName, self::SKIP_TAGS, true)) {
                    $skipContent = self::consumeSkipBlock($tokens, $i, $tagName);
                    if ($skipContent !== null) {
                        $nodes[] = new StringNode($skipContent);
                        continue;
                    }
                }

                $node = HTMLNode::fromTokens($tokens, $i);
                if ($node !== null) {
                    $nodes[] = $node;
                    continue;
                }
            }

            // Sonst normalen Text sammeln
            $buffer = '';
            $startIndex = $i;

            while ($i < $length) {
                $curr = $tokens[$i];

                if (
                    $curr[0] === Token::LT &&
                    isset($tokens[$i + 1]) &&
                    in_array($tokens[$i + 1][0], [Token::IDENTIFIER, Token::DIV, Token::NOT])
                ) {
                    break;
                }

                if (is_array($curr[1])) {
                    if ($buffer !== '') {
                        $nodes[] = new StringNode($buffer);
                        $buffer = '';
                    }
                    $exprNode = ExpressionParser::parse($curr[1]);
                    if ($exprNode !== null) {
                        $nodes[] = $exprNode;
                    }
                } else {
                    $buffer .= $curr[1];
                }

                $i++;
            }

            if ($buffer !== '') {
                $nodes[] = new StringNode($buffer);
                $buffer = '';
            }

            // Verhindere Endlosschleife
            if ($startIndex === $i) {
                $i++;
            }
        }

        return $nodes;
    }

    public static function readTagName(array $tokens, int &$pos): ?string
	{
		$name = '';

		$expectsIdentifier = true;

		while (isset($tokens[$pos])) {
			$token = $tokens[$pos];
			
			switch( $token[0] ){
				case Token::IDENTIFIER:{
					if( $expectsIdentifier ){
						$name .= $token[1];
						$expectsIdentifier = false;
					} else
						break 2;
				}break;
				case Token::MINUS:{
					$name .= Token::MINUS->value;
					$expectsIdentifier = true;
				}break;
				default:
					break 2;
			}
			
			$pos++;
		}
		return $name === '' ? null : strtolower($name);
	}


    private static function consumeSkipBlock(array $tokens, int &$i, string $tagName): ?string
    {
        $length = count($tokens);
        $buffer = '';
        $depth = 0;

        while ($i < $length) {
            $token = $tokens[$i];
            $buffer .= is_array($token) ? $token[1] : $token;

            // Öffnendes Tag
            if (
                $token[0] === Token::LT &&
                isset($tokens[$i + 1], $tokens[$i + 2]) &&
                $tokens[$i + 1][0] === Token::IDENTIFIER &&
                strtolower($tokens[$i + 1][1]) === $tagName
            ) {
                $depth++;
            }

            // Schließendes Tag
            if (
                $token[0] === Token::LT &&
                isset($tokens[$i + 1], $tokens[$i + 2], $tokens[$i + 3]) &&
                $tokens[$i + 1][0] === Token::DIV &&
                strtolower($tokens[$i + 2][1]) === $tagName &&
                $tokens[$i + 3][0] === Token::GT
            ) {
                $depth--;

                if ($depth === 0) {
                    // Schließe das Tag vollständig ab
                    $buffer .= is_array($tokens[$i + 1]) ? $tokens[$i + 1][1] : $tokens[$i + 1];
                    $buffer .= is_array($tokens[$i + 2]) ? $tokens[$i + 2][1] : $tokens[$i + 2];
                    $buffer .= is_array($tokens[$i + 3]) ? $tokens[$i + 3][1] : $tokens[$i + 3];
                    $i += 4;
                    break;
                }
            }

            $i++;
        }

        return $buffer;
    }

    private static function consumeComment(array $tokens, int &$i): ?string
    {
        $length = count($tokens);
        $buffer = '';
        $i += 3; // Überspringe '<!--'

        while ($i < $length) {
            $token = $tokens[$i];
            if (
                $token[0] === Token::MINUS &&
                isset($tokens[$i + 1], $tokens[$i + 2]) &&
                $tokens[$i + 1][0] === Token::MINUS &&
                $tokens[$i + 2][0] === Token::GT
            ) {
                $i += 3;
                break;
            }

            $buffer .= is_array($token) ? $token[1] : $token;
            $i++;
        }

        return $buffer;
    }

    private static function consumeCDATA(array $tokens, int &$i): ?string
    {
        $length = count($tokens);
        $buffer = '';
        // Wir gehen davon aus, dass <![CDATA[ bereits erkannt ist und $i zeigt auf '<'

        // Springe über <![CDATA[
        $i += 9;

        while ($i < $length) {
            $token = $tokens[$i];

            if (
                $token[0] === Token::CLOSE_SQUARE &&
                isset($tokens[$i + 1], $tokens[$i + 2]) &&
                $tokens[$i + 1][0] === Token::CLOSE_SQUARE &&
                $tokens[$i + 2][0] === Token::GT
            ) {
                $i += 3;
                break;
            }

            $buffer .= is_array($token) ? $token[1] : $token;
            $i++;
        }

        return $buffer;
    }
}
