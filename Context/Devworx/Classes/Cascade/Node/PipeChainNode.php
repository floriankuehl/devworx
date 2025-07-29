<?php

namespace Cascade\Node;

use Cascade\Parser\NodeFactory;
use Cascade\Parser\Tokenizer;
use Cascade\Parser\ExpressionParser;
use Cascade\Enums\Token;
use Cascade\Runtime\Context;
use Cascade\Interfaces\ITokenPattern;

class PipeChainNode extends AbstractNode implements ITokenPattern
{
    const PIPECHAR = '->';

    public const PATTERN = [
        Token::VALUE,  // beliebiger Wert oder Ausdruck
        Token::PIPE     // muss mindestens ein PIPE enthalten
    ];

    /**
     * @param AbstractNode[] $nodes
     */
    public function __construct(protected array $nodes)
    {
    }

	public function getNodes(): array {
		return $this->nodes;
	}

    public static function getToken(): Token
    {
        return Token::PIPE;
    }

    public static function matches(array $tokens): bool
    {
        $pipeCount = 0;
        $depth = 0;

        foreach ($tokens as [$type, $value]) {
            if (in_array($type, Tokenizer::OPENER)) {
                $depth++;
            }

            if (in_array($type, Tokenizer::CLOSER)) {
                $depth--;
            }

            if ($type === Token::PIPE && $depth === 0) {
                $pipeCount++;
            }
        }

        // Nur als PipeChain behandeln, wenn mindestens 1 Top-Level-Pipe vorhanden ist
        return $pipeCount > 0;
    }

    public static function fromTokens(array $tokens, int &$i=0): ?PipeChainNode
    {
        if (!self::matches($tokens)) {
			trigger_error("not matching");
            return null;
        }

        $groups = self::splitByTopLevelPipe($tokens);
        if (count($groups) < 2) {
			trigger_error("groups < 2");
            return null;
        }

		//echo \Devworx\Utility\DebugUtility::var_dump($groups,'PipeChain::groups',__METHOD__,__LINE__);
		$i = 0;

        $nodes = [];
        foreach ($groups as $group) {
            $node = ExpressionParser::parse($group);
			
			/*
			echo \Devworx\Utility\DebugUtility::var_dump([
				'group' => $group,
				'node' => $node
			],'The Group',__METHOD__,__LINE__);
			*/
            if (!$node) {
				echo \Devworx\Utility\DebugUtility::var_dump(['group'=>$group,$i],'Group not found',__METHOD__,__LINE__);
                return null;
            }
            $nodes[] = $node;
        }

        return new self($nodes);
    }

    public static function splitByTopLevelPipe(array $tokens): array
    {
        $groups = [];
        $current = [];
        $depth = 0;

        foreach ($tokens as [$type, $value]) {
            if (in_array($type, Tokenizer::OPENER)) {
                $depth++;
            } elseif (in_array($type, Tokenizer::CLOSER)) {
                $depth--;
            }

            if ($type === Token::PIPE && $depth === 0) {
                $groups[] = $current;
                $current = [];
            } else {
                $current[] = [$type, $value];
            }
        }

        if (!empty($current)) {
            $groups[] = $current;
        }

        return $groups;
    }

    public function evaluate(Context|array &$context,mixed $input=null): mixed
    {
        $result = $this->nodes[0]->evaluate($context,$input);
        for ($i = 1; $i < count($this->nodes); $i++) {
            $next = $this->nodes[$i];
            $result = $next->evaluate($context,$result);
        }
        return $result;
    }

    public function compile(string $contextVar = '$context', string $input = 'null'): string
	{
		$compiled = $this->nodes[0]->compile($contextVar, $input);
		$nodeCount = count($this->nodes);

		for ($i = 1; $i < $nodeCount; $i++) {
			$compiled = $this->nodes[$i]->compile($contextVar, $compiled);
		}

		return $compiled;
	}

}
