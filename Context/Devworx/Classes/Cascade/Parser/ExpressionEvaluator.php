<?php

namespace Devworx\Cascade\Runtime;

use Cascade\Runtime\SystemFunctions;

use Cascade\Node\BoolNode;
use Cascade\Node\NumberNode;
use Cascade\Node\ConstantNode;
use Cascade\Node\VariableNode;
use Cascade\Node\ArrayNode;
use Cascade\Node\ObjectNode;

use Cascade\Node\BinaryOperatorNode;
use Cascade\Node\FunctionCallNode;

class ExpressionEvaluator
{
    protected array $context;

    public function __construct(array $context = [])
    {
        $this->context = $context;
    }

    /**
     * Wertet einen Node (AST) im aktuellen Kontext aus.
     *
     * @param AbstractNode $node
     * @return mixed
     */
    public function evaluate(AbstractNode $node)
    {
        return $this->evaluateNode($node);
    }

    protected function evaluateNode(AbstractNode $node)
    {
        // Je nach Node-Typ unterschiedliche Auswertung
        $class = get_class($node);

        switch (true) {
            case $node instanceof BoolNode:
                return $node->getValue();

            case $node instanceof NumberNode:
                return $node->getValue();

            case $node instanceof NullNode:
                return null;

            case $node instanceof ConstantNode:
                return $this->resolveConstant($node->getValue());

            case $node instanceof VariableNode:
                return $this->resolveVariable($node->getName());

            case $node instanceof ArrayNode:
                $result = [];
                foreach ($node->getItems() as $keyNode => $valueNode) {
                    $key = is_int($keyNode) ? $keyNode : $this->evaluateNode($keyNode);
                    $result[$key] = $this->evaluateNode($valueNode);
                }
                return $result;

            case $node instanceof ObjectNode:
                $result = new \stdClass();
                foreach ($node->getProperties() as $keyNode => $valueNode) {
                    $key = is_int($keyNode) ? $keyNode : $this->evaluateNode($keyNode);
                    $result->$key = $this->evaluateNode($valueNode);
                }
                return $result;

            case $node instanceof BinaryOperatorNode:
                $left = $this->evaluateNode($node->getLeft());
                $right = $this->evaluateNode($node->getRight());
                return SystemFunctions::evaluateBinaryOperator($node->getOperator(), $left, $right);

            case $node instanceof FunctionCallNode:
                $args = [];
                foreach ($node->getArguments() as $arg) {
                    $args[] = $this->evaluateNode($arg);
                }
                $funcName = $node->getFunctionName();
                return SystemFunctions::call($funcName, $args);

            // Weitere Node-Typen hier abfangen...

            default:
                throw new \RuntimeException("ExpressionEvaluator: Unbekannter Node-Typ: $class");
        }
    }

    protected function resolveVariable(string $name)
    {
        // Einfache Context-Variable lookup
        return $this->context[$name] ?? null;
    }

    protected function resolveConstant(string $name)
    {
        // Beispiel für Konstanten wie true, false, null
        switch (strtolower($name)) {
            case 'true': return true;
            case 'false': return false;
            case 'null': return null;
            default:
                throw new \RuntimeException("Unbekannte Konstante: $name");
        }
    }
}
