<?php

namespace Cascade\Parser;

use Cascade\Node\ViewHelperNode;
use Cascade\Node\StringNode;
use Cascade\Enums\Token;

class ViewHelperParser
{
    public static function parse(array $tokens, int &$i = 0): ?ViewHelperNode
    {
        $start = $i;

        // Prüfe auf <d:
        if (
            $tokens[$i][0] === Token::LT &&
            isset($tokens[$i + 1], $tokens[$i + 2]) &&
            $tokens[$i + 1][0] === Token::IDENTIFIER &&
            $tokens[$i + 2][0] === Token::COLON
        ) {
            $i += 3; // Überspringe <d:

            $name = HTMLParser::readTagName($tokens, $i);
            if ($name === null) {
                return null;
            }

            $attributes = self::readAttributes($tokens, $i);

            // Selbstschließendes Tag?
            if ($tokens[$i][0] === Token::DIV && $tokens[$i + 1][0] === Token::GT) {
                $i += 2;
                return new ViewHelperNode($name, $attributes, []);
            }

            // Öffnendes Tag mit Inhalt
            if ($tokens[$i][0] === Token::GT) {
                $i++;

                $children = [];
                while ($i < count($tokens)) {
                    if (self::isClosingTag($tokens, $i, $name)) {
                        $i += 4; // </d:name>
                        break;
                    }

                    // Delegiere an HTMLParser oder rekursiv ViewHelperParser
                    $child = ViewHelperParser::parse($tokens, $i) ??
                             HTMLParser::parse($tokens, $i)[0] ?? null;

                    if ($child !== null) {
                        $children[] = $child;
                    }
                }

                return new HTMLViewHelperNode($name, $attributes, $children);
            }
        }

        $i = $start;
        return null;
    }

    private static function readAttributes(array $tokens, int &$i): array
    {
        $attributes = [];

        while ($i < count($tokens)) {
            if ($tokens[$i][0] === Token::GT || $tokens[$i][0] === Token::DIV) {
                break;
            }

            if ($tokens[$i][0] === Token::IDENTIFIER) {
                $key = $tokens[$i][1];
                $i++;

                if (isset($tokens[$i]) && $tokens[$i][0] === Token::EQUALS) {
                    $i++;
                    if (isset($tokens[$i]) && $tokens[$i][0] === Token::STRING) {
                        $value = $tokens[$i][1];
                        $attributes[$key] = $value;
                        $i++;
                    }
                }
            } else {
                $i++;
            }
        }

        return $attributes;
    }

    private static function isClosingTag(array $tokens, int $i, string $name): bool
    {
        return isset($tokens[$i], $tokens[$i + 1], $tokens[$i + 2], $tokens[$i + 3]) &&
               $tokens[$i][0] === Token::LT &&
               $tokens[$i + 1][0] === Token::DIV &&
               $tokens[$i + 2][0] === Token::IDENTIFIER &&
               $tokens[$i + 3][0] === Token::COLON &&
               isset($tokens[$i + 4]) &&
               $tokens[$i + 4][1] === $name;
    }
}
