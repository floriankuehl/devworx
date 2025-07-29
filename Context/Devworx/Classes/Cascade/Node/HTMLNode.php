<?php

namespace Cascade\Node;

use Cascade\Enums\Token;
use Cascade\Interfaces\INode;
use Cascade\Interfaces\ITokenPattern;
use Cascade\Parser\NodeFactory;
use Cascade\Parser\HTMLParser;
use Cascade\Parser\Tokenizer;
use Cascade\Parser\ExpressionParser;
use Cascade\Runtime\Context;

class HTMLNode extends AbstractNode implements ITokenPattern
{
    public const SKIP_TAGS = [
        'code', 'style', 'script', 'devworx-debug'
    ];
    
    public const SELF_CLOSING = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'param', 'source', 'track', 'wbr',
    ];
    
    public const PATTERN = [
        Token::LT,
        Token::IDENTIFIER
    ];
    
    public string $tagName;
    /** @var array<string, INode|string|bool> */
    public array $attributes = [];
    /** @var array<INode|string> */
    public array $children = [];

    public function __construct(string $tagName, array $attributes = [], array $children = [])
    {
        $this->tagName = strtolower($tagName);
        $this->attributes = $attributes;
        $this->children = $children;
    }

    public static function getToken(): Token
    {
        return Token::HTML;
    }

    public static function matches(array $tokens): bool
    {
        return NodeFactory::checkTokenPattern($tokens, self::PATTERN);
    }

    /**
     * Parst ein HTML-Tag mit Attributen.
     * @param array $tokens
     * @param int $i Index im Token-Array, wird angepasst
     * @return static|null
     */
    public static function fromTokens(array $tokens, int &$i = 0): ?self
	{
		$length = count($tokens);
		if (!isset($tokens[$i]) || $tokens[$i][0] !== Token::LT) {
			return null;
		}
		$pos = $i + 1;

		$isClosingTag = false;
		if (isset($tokens[$pos]) && $tokens[$pos][0] === Token::DIV) {
			// Schließendes Tag </tag>
			$isClosingTag = true;
			$pos++;
		}

		// Tagname lesen
		$tagName = HTMLParser::readTagName($tokens,$pos);
		if (!$tagName) {
			return null; // Kein gültiger Tagname
		}
		
		if ($isClosingTag) {
			// Schließendes Tag, einfach bis '>' skippen und zurückgeben
			while ($pos < $length && $tokens[$pos][0] !== Token::GT) {
				$pos++;
			}
			if ($pos < $length && $tokens[$pos][0] === Token::GT) {
				$pos++;
			}
			$i = $pos;
			return null;
		}

		// Attribute parsen
		$attributes = ParameterParser::parseAttributes($tokens,$pos);
		/*
		$attributes = [];
		while ($pos < $length) {
			$token = $tokens[$pos];

			if ($token[0] === Token::GT) {
				$pos++;
				break;
			}

			if ($token[0] === Token::IDENTIFIER) {
				$attrName = $token[1];
				$pos++;

				if ($pos < $length && $tokens[$pos][0] === Token::ASSIGN) {
					$pos++;

					if ($pos < $length) {
						$valToken = $tokens[$pos];
						
						switch( $valToken[0] ){
							case Token::STRING:{
								if( 
									str_contains($valToken[1], Token::OPEN->value) && 
									str_contains($valToken[1], Token::CLOSE->value) 
								){
									$attributes[$attrName] = ExpressionParser::parseMixed($valToken[1]);
								} else
									$attributes[$attrName] = $valToken[1];
								$pos++;
							} break;
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
					// Attribut ohne Wert (z. B. <input disabled>)
					$attributes[$attrName] = true;
				}

			} else {
				$pos++;
			}
		}
		*/
		$children = [];

		// Selbstschließende Tags haben keine Kinder
		if (!in_array($tagName, self::SELF_CLOSING, true)) {
			while ($pos < $length) {
				$token = $tokens[$pos];

				if ($token[0] === Token::LT) {
					// Möglicherweise schließendes Tag oder neues Kind-Tag
					if (
						isset($tokens[$pos + 1]) &&
						$tokens[$pos + 1][0] === Token::DIV
					) {
						// Schließendes Tag gefunden
						// Prüfen, ob es das richtige schließende Tag ist
						$closingTagPos = $pos + 2;
						$closingTagName = '';
						while (isset($tokens[$closingTagPos])) {
							$t = $tokens[$closingTagPos];
							if ($t[0] === Token::IDENTIFIER) {
								$closingTagName .= $t[1];
								$closingTagPos++;
							} elseif ($t[0] === Token::MINUS) {
								$closingTagName .= '-';
								$closingTagPos++;
							} else {
								break;
							}
						}
						$closingTagName = strtolower($closingTagName);

						if ($closingTagName === $tagName) {
							// Springe über das schließende Tag '>'
							while ($closingTagPos < $length && $tokens[$closingTagPos][0] !== Token::GT) {
								$closingTagPos++;
							}
							if ($closingTagPos < $length && $tokens[$closingTagPos][0] === Token::GT) {
								$closingTagPos++;
							}
							$pos = $closingTagPos;
							break;
						}
					}

					// Neues Kind-Element (HTMLNode)
					$childNode = self::fromTokens($tokens, $pos);
					if ($childNode !== null) {
						$children[] = $childNode;
						continue;
					}

					// Falls kein HTMLNode, könnte es ein Textknoten sein
					// Ein einfaches String-Token oder anderes behandeln wir unten
				}

				// Text oder andere Tokens als String sammeln
				$text = '';
				while ($pos < $length && (!isset($tokens[$pos]) || $tokens[$pos][0] !== Token::LT)) {
					$token = $tokens[$pos];
					$type = $token[0] ?? null;
					$value = $token[1] ?? null;
					
					if ($type === Token::WHITESPACE || $type === Token::IDENTIFIER || $type === Token::STRING) {
						$text .= $value;
						$pos++;
						continue;
					}
					
					if (is_array($value)) {
						if ($text !== '') {
							$children[] = new StringNode($text);
							$text = '';
						}

						$exprNode = ExpressionParser::parse($value);
						$children[] = $exprNode;

						$pos++;
						continue;
					}

					$text .= (string)$value;
					$pos++;
				}

				// Letzten Textblock anhängen
				if ($text !== '') {
					$children[] = $text;
				}

			}
		}

		$i = $pos;
		return new self($tagName, $attributes, $children);
	}


    public function isSelfClosing(): bool
    {
        return in_array($this->tagName, self::SELF_CLOSING, true);
    }

    public function compile(string $contextVar = '$context', string $input = 'null'): string
    {
        $compiledParts = [];

        // Öffnender Tag + Attribute
        $compiledParts[] = var_export('<' . $this->tagName, true);

        foreach ($this->attributes as $key => $value) {
            $compiledParts[] = var_export(' ' . $key . '="', true);

            if (is_array($value)) {
                foreach ($value as $part) {
                    $compiledParts[] = $part instanceof INode ? $part->compile($contextVar, $input) : var_export($part, true);
                }
            } elseif ($value instanceof INode) {
				$compiledValue = $value->compile($contextVar, $input);
				if( empty($compiledValue) ) $compiledValue = "''";
				$compiledParts[] = $compiledValue;
            } elseif (is_bool($value) && $value === true) {
                // Attribut ohne Wert: disabled etc.
                $compiledParts[count($compiledParts) - 1] = var_export(' ' . $key, true);
                continue;
            } else {
                $compiledParts[] = var_export($value, true);
            }

            $compiledParts[] = var_export('"', true);
        }

        $compiledParts[] = var_export('>', true);

        // Kinder
        foreach ($this->children as $child) {
            $childCompiled = $child instanceof INode ? $child->compile($contextVar, $input) : (string) $child;
            if ($childCompiled !== "''" && $childCompiled !== '') {
                $compiledParts[] = $childCompiled;
            }
        }

        // Schließender Tag
        if (!$this->isSelfClosing()) {
            $compiledParts[] = var_export('</' . $this->tagName . '>', true);
        }

        return implode(', ', $compiledParts);
    }

    public function evaluate(Context|array &$context, mixed $input = null): mixed
    {
        $evaluatedAttributes = [];

        foreach ($this->attributes as $name => $value) {
            $valStr = '';
            if (is_array($value)) {
                foreach ($value as $node) {
                    $valStr .= $node instanceof INode ? $node->evaluate($context) : (string)$node;
                }
            } elseif ($value instanceof INode) {
                $valStr = $value->evaluate($context);
            } elseif (is_bool($value) && $value === true) {
                $valStr = '';
            } else {
                $valStr = (string)$value;
            }

            if ($valStr !== '') {
                $escaped = htmlspecialchars($valStr, ENT_QUOTES | ENT_HTML5);
                $evaluatedAttributes[] = $name . '="' . $escaped . '"';
            } else {
                // Attribut ohne Wert (bool true) oder leerer String
                $evaluatedAttributes[] = $name;
            }
        }

        $evaluatedChildren = '';
        foreach ($this->children as $child) {
            $evaluatedChildren .= $child instanceof INode ? $child->evaluate($context) : (string)$child;
        }

        $attrString = empty($evaluatedAttributes) ? '' : ' ' . implode(' ', $evaluatedAttributes);

        if ($evaluatedChildren === '') {
            return $this->isSelfClosing()
                ? "<{$this->tagName}{$attrString}/>"
                : "<{$this->tagName}{$attrString}></{$this->tagName}>";
        }

        return "<{$this->tagName}{$attrString}>{$evaluatedChildren}</{$this->tagName}>";
    }
}
