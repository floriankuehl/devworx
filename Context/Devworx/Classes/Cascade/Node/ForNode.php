<?php

namespace Cascade\Parser\Node;

use Cascade\Runtime\Context;
use Cascade\Parser\ParameterParser;
use Cascade\Enums\Token;

class ForNode extends AbstractNode
{
    protected string $collectionExpr;
    protected string $asVariableName;
    protected ?string $keyName;
    protected ?string $iterationName;
    /** @var AbstractNode[] */
    protected array $bodyNodes;

    public function __construct(
        string $collectionExpr,
        string $asVariableName,
        ?string $keyName = null,
        ?string $iterationName = null,
        array $bodyNodes = []
    ) {
        $this->collectionExpr = $collectionExpr;
        $this->asVariableName = $asVariableName;
        $this->keyName = $keyName;
        $this->iterationName = $iterationName;
        $this->bodyNodes = $bodyNodes;
    }
	
	public static function getToken(): Token {
		return Token::EXPESSION;
	}

    protected function buildIteratorCode(): string
    {
        $collection = $this->collectionExpr;
        return sprintf(
            '[
                "index" => $index,
                "cycle" => $index + 1,
                "isEven" => $index %% 2 === 0,
                "isOdd" => $index %% 2 === 1,
                "isFirst" => $index === 0,
                "isLast" => ($index + 1) === count(%s)
            ]',
            $collection
        );
    }

	public function evaluate(Context|array &$context,mixed $input=null): mixed
	{
		// Den Ausdruck für die Collection auswerten
		$collection = (new ExpressionEvaluator())->evaluate($this->collectionExpr, $context);

		if (!is_iterable($collection)) {
			throw new \RuntimeException("Expression '{$this->collectionExpr}' did not evaluate to an iterable.");
		}

		$result = [];

		$index = 0;
		$count = is_countable($collection) ? count($collection) : null;

		$localContext = new Context();
		$localContext->setAll( $context->toArray() ); //nötig?
		
		foreach ($collection as $key => $item) {
			// Kontext für diese Iteration (lokal klonen)

			// Setze Schleifenvariable
			$localContext->set($this->asVariableName,$item);

			// Optional Schlüsselvariable setzen
			if ($this->keyName !== null) {
				$localContext->set($this->keyName,$key);
			}

			// Optional Iterationsinfos
			if ($this->iterationName !== null) {
				$localContext->set($this->iterationName, [
					'index' => $index,
					'cycle' => $index + 1,
					'isEven' => ($index % 2) === 0,
					'isOdd' => ($index % 2) === 1,
					'isFirst' => $index === 0,
					'isLast' => $count !== null ? ($index === $count - 1) : false,
				]);
			}

			// Evaluieren der Body-Nodes in diesem Kontext
			foreach ($this->bodyNodes as $node) {
				// Die Nodes könnten Ergebnisse zurückgeben, z.B. Stringausgaben sammeln
				$evalResult = $node->evaluate($localContext,$input);

				if ($evalResult !== null) {
					$result[] = $evalResult;
				}
			}

			$index++;
		}

		// Ergebnis zusammenführen, z.B. Strings verbinden, oder als Array zurückgeben
		// Hier einfach als Array zurückgeben
		return $result;
	}

    public function compile(string $contextVar='$context',string $input='null'): string
    {
        $loopVar = '$__' . $this->asVariableName;
        $keyVar = $this->keyName ? '$' . $this->keyName : null;
        $iterationVar = $this->iterationName ? '$' . $this->iterationName : null;

        $lines = [];
        $lines[] = '$index = 0;';
        $lines[] = "foreach ({$this->collectionExpr} as " . ($keyVar ? "{$keyVar} => " : '') . "{$loopVar}) {";

        // Set the current item into the context
        $lines[] = "    {$contextVar}->set('{$this->asVariableName}', {$loopVar});";

        // If keyName is set, assign it into context
        if ($keyVar) {
            $lines[] = "    {$contextVar}->set('{$this->keyName}', {$keyVar});";
        }

        // If iterationName is set, assign iteration info
        if ($iterationVar) {
            $iteratorCode = $this->buildIteratorCode();
            $lines[] = "    {$contextVar}->set('{$this->iterationName}', {$iteratorCode});";
        }

        // Compile body nodes
        foreach ($this->bodyNodes as $node) {
            $compiledBody = $node->compile($context);
            $lines[] = "    {$compiledBody}";
        }

        $lines[] = '    $index++;';
        $lines[] = '}';

        return implode("\n", $lines);
    }

    public static function fromTokens(array $tokens, int &$i=0): ?AbstractNode
    {
        if (count($tokens) === 0) {
            return null;
        }

        $first = strtolower($tokens[0]['value'] ?? '');

        if ($first !== 'for') {
            return null;
        }

        $argsStr = trim(join('', array_column($tokens, 'value')));
        if (preg_match('/^for\s*\((.*)\)$/i', $argsStr, $matches)) {
            $args = ParameterParser::parse($matches[1]);

            return new self(
                $args['each'] ?? '',
                $args['as'] ?? '',
                $args['key'] ?? null,
                $args['iteration'] ?? null,
                [] // Body nodes müssen später gesetzt werden
            );
        }

        return null;
    }
}
