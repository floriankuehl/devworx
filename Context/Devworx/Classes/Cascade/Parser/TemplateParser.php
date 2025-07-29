<?php

namespace Cascade\Parser;

use Devworx\Interfaces\IParser;
use Cascade\Node\RootNode;
use Cascade\Node\StringNode;
use Cascade\Node\HTMLNode;
use Cascade\Node\ExpressionNode;
use Cascade\Enums\Token;
use Cascade\Enums\ParserMode;

class TemplateParser implements IParser
{
	const IGNORE_TAGS = ['script', 'style', 'code', 'devworx-debug'];

	protected ParserMode $mode = ParserMode::Html;

	public function parse(string $template): mixed
	{
		$this->mode = ParserMode::Html;
		$length = strlen($template);
		$offset = 0;
		$buffer = '';
		$nodes = [];

		while ($offset < $length) {
			$char = $template[$offset];

			// Ausdruck: {...}
			if ($char === Token::OPEN->value && isset($template[$offset + 1]) && $template[$offset + 1] !== Token::OPEN->value) {
				if ($buffer !== '') {
					$nodes = array_merge($nodes, $this->parseWithExpressions($buffer));
					$buffer = '';
				}

				$expression = $this->extractBracketedContent($template, $offset);
				$expression = trim($expression);
				$tokens = Tokenizer::tokenize($expression);
				$node = NodeFactory::fromTokens($tokens);
				$nodes[] = $node ?? new StringNode("{{$expression}}");

				continue;
			}

			// Ignorierte Tags?
			foreach (self::IGNORE_TAGS as $tag) {
				if ($this->startsWithTag($template, $offset, $tag)) {
					if ($buffer !== '') {
						$nodes = array_merge($nodes, $this->parseWithExpressions($buffer));
						$buffer = '';
					}

					$tagContent = $this->consumeUntilTagEnd($template, $offset, $tag);
					$nodes[] = new StringNode($tagContent); // Nicht weiter parsen
					continue 2; // zurück zur while-Schleife
				}
			}

			// HTML Tag?
			if ($char === Token::LT->value) {
				$node = $this->parseHTMLNode($template, $offset);
				if ($node) {
					if ($buffer !== '') {
						$nodes = array_merge($nodes, $this->parseWithExpressions($buffer));
						$buffer = '';
					}

					$nodes[] = $node;
					continue;
				}
			}

			$buffer .= $char;
			$offset++;
		}

		if ($buffer !== '') {
			$nodes = array_merge($nodes, $this->parseWithExpressions($buffer));
		}

		return count($nodes) === 1
			? $nodes[0]
			: new ExpressionNode($nodes);
	}

	public function parseHTMLNode(string $template, int &$offset): ?HTMLNode
	{
		if (!preg_match('/\G<([a-zA-Z][a-zA-Z0-9\-]*)\s*([^>]*)>/A', $template, $match, 0, $offset)) {
			return null;
		}

		$tagName = strtolower($match[1]);
		$attrString = $match[2];
		$offset += strlen($match[0]);

		$attributes = $this->parseAttributes($attrString);

		$closeTag = "</$tagName>";
		$innerStart = $offset;
		$inner = '';
		$depth = 1;

		while ($offset < strlen($template)) {
			$nextOpen = stripos($template, "<$tagName", $offset);
			$nextClose = stripos($template, $closeTag, $offset);

			if ($nextClose === false) {
				// Kein End-Tag gefunden – defensiv abbrechen
				$inner = substr($template, $innerStart);
				$offset = strlen($template);
				break;
			}

			if ($nextOpen !== false && $nextOpen < $nextClose) {
				$depth++;
				$offset = $nextOpen + 1;
			} else {
				$depth--;
				if ($depth === 0) {
					$inner = substr($template, $innerStart, $nextClose - $innerStart);
					$offset = $nextClose + strlen($closeTag);
					break;
				}
				$offset = $nextClose + 1;
			}
		}

		if (in_array($tagName, self::IGNORE_TAGS)) {
			return new HTMLNode($tagName, $attributes, [new StringNode($inner)]);
		}

		$children = $this->parse($inner);
		return new HTMLNode($tagName, $attributes, is_array($children) ? $children : [$children]);
	}

	public function parseExpression(string $expression): ?\Cascade\Interfaces\INode
	{
		$tokens = Tokenizer::tokenize($expression);
		return ExpressionParser::parse($tokens);
	}

	public function parseAttributes(string $attrString): array
	{
		$attributes = [];

		preg_match_all('/([a-zA-Z_:][a-zA-Z0-9:_.-]*)\s*=\s*"([^"]*)"/', $attrString, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			[$full, $key, $val] = $match;
			$nodes = $this->parseWithExpressions($val);
			$attributes[$key] = count($nodes) === 1 ? $nodes[0] : new ExpressionNode($nodes);
		}

		return $attributes;
	}

	public function parseWithExpressions(string $template): array
	{
		$parts = preg_split('/({{.*?}}|{[^{}]+})/', $template, -1, PREG_SPLIT_DELIM_CAPTURE);
		$nodes = [];

		foreach ($parts as $part) {
			if ($part === '' || $part === null) continue;

			if (preg_match('/^{(.*?)}$/s', $part, $matches)) {
				$expression = $matches[1];
				$node = $this->parseExpression($expression);
				$nodes[] = $node ?? new StringNode($part);
			} else {
				$nodes[] = new StringNode($part); // whitespace etc. bleibt erhalten
			}
		}

		return $nodes;
	}

	public function extractBracketedContent(string $input, int &$offset, string $open = '{', string $close = '}'): string
	{
		$depth = 0;
		$start = $offset;
		$length = strlen($input);

		while ($offset < $length) {
			if ($input[$offset] === $open) {
				$depth++;
			} elseif ($input[$offset] === $close) {
				$depth--;
				if ($depth === 0) {
					$offset++;
					return substr($input, $start + 1, $offset - $start - 2);
				}
			}
			$offset++;
		}

		throw new \RuntimeException("Unmatched brackets in input starting at offset $start");
	}

	public function startsWithTag(string $template, int $offset, string $tag): bool
	{
		return stripos(substr($template, $offset), "<$tag") === 0;
	}

	public function consumeUntilTagEnd(string &$template, int &$offset, string $tag): string
	{
		$start = $offset;
		$endTag = "</$tag>";
		$endPos = stripos($template, $endTag, $offset);

		if ($endPos === false) {
			$offset = strlen($template);
			return substr($template, $start);
		}

		$offset = $endPos + strlen($endTag);
		return substr($template, $start, $offset - $start);
	}

	public function compile(mixed $parsed, string $contextVar = '$context'): string
	{
		if ($parsed instanceof RootNode) {
			return $parsed->compile($contextVar);
		}
		trigger_error("Expected RootNode, got " . (is_object($parsed) ? get_class($parsed) : gettype($parsed)), E_USER_ERROR);
		return '';
	}

	public function parseCompile(string $template, string $contextVar = '$context'): string
	{
		$parsed = $this->parse($template);
		return $parsed->compile($contextVar);
	}
}
