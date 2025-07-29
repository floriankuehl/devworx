<?php

namespace Cascade\Node;

use Cascade\Enums\Token;
use Cascade\Interfaces\IValueNode;
use Cascade\Interfaces\INode;
use Cascade\Runtime\Context;

abstract class AbstractValueNode extends AbstractNode implements IValueNode
{
    protected mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

	public static function getToken(): Token {
		return Token::VALUE;
	}

	public static function getValueType(): string {
		return ( self::getToken() )->value;
	}

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }
	
    /**
     * Gibt eine PHP-kompatible Darstellung zurück, z. B. für Serialisierung oder Debugging.
     */
    public function toPhpString(): string
    {
        return var_export($this->value, true);
    }

    /**
     * Optional: Gibt den Typ des Werts zurück (z. B. für statische Prüfungen).
     */
    public function getLiteralType(): string
    {
        return gettype($this->value);
    }
	
	public function compile(string $contextVar = '$context',string $input='null'): string
    {
        return var_export($this->value, true);
    }

    public function evaluate(Context|array &$context,mixed $input=null): mixed
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return var_export($this->value, true);
    }
	
	
}
