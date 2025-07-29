<?php

namespace Cascade\Node;

use Cascade\Runtime\Context;
use Cascade\Interfaces\INode;
use Cascade\Enums\Token;

abstract class AbstractNode implements INode
{
    /**
     * Kompiliert den Node zu ausführbarem PHP.
     */
    abstract public function compile(string $contextVar = '$context',string $input='null'): string;

    /**
     * Wert des Nodes zur Laufzeit evaluieren.
     */
    abstract public function evaluate(Context|array &$context,mixed $input=null): mixed;

    /**
     * Erzeugt einen Node aus einem Token-Array.
     */
    public static function fromTokens(array $tokens, int &$i=0): ?self
    {
        throw new \LogicException('fromTokens() muss in der Subklasse implementiert werden.');
    }

    /**
     * Debug-Hilfe für AST-Dumps.
     */
    public function dump(): array
    {
        return [
            'type' => static::class,
            'data' => get_object_vars($this),
        ];
    }

    /**
     * Zugriff auf Kontextdaten.
     */
    protected function getContextValue(Context|array $context, string $key, $default = null)
    {
        if ($context instanceof Context) {
            return $context->get($key, $default);
        }

        $segments = explode('.', $key);
        $value = $context;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Jeder Node muss seinen Typ definieren (für Pattern Matching etc.).
     */
    public static function getType(): string
    {
        throw new \LogicException('getType() muss in der Subklasse überschrieben werden.');
    }
	
	/**
     * Jeder Node muss seinen Typ definieren (für Pattern Matching etc.).
     */
    public static function getToken(): Token
    {
        throw new \LogicException('getToken() muss in der Subklasse überschrieben werden.');
    }
}
