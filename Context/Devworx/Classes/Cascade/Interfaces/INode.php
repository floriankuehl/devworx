<?php

namespace Cascade\Interfaces;

use Cascade\Runtime\Context;
use Cascade\Enums\Token;

interface INode
{
    /**
     * Kompiliert den Node zu ausführbarem PHP-Code.
     *
     * @param string $contextVar Der Variablenname für den Kontext (z.B. '$context').
	 * @param string $input Der Inhalt für den Node-Input
     * @return string
     */
    public function compile(string $contextVar = '$context',string $input='null'): string;

    /**
     * Evaluierung des Nodes zur Laufzeit.
     *
     * @param Context|array $context
     * @return mixed
     */
    public function evaluate(Context|array &$context,mixed $input=null): mixed;

    /**
     * Optional: Gibt Debug-Daten für den Node zurück.
     *
     * @return array
     */
    public function dump(): array;

    /**
     * Erzeugt eine Instanz aus einem Token-Array.
     *
     * @param array $tokens
	 * @param int $i reference to a position inside the tokens (recursive calls)
     * @return static|null
     */
    public static function fromTokens(array $tokens, int &$i=0): ?self;

    /**
     * Gibt den logischen Typ des Nodes zurück (z.B. 'string', 'number', etc.)
     *
     * @return string
     */
    public static function getType(): string;
	
	/**
     * Gibt den Token des Nodes zurück
     *
     * @return Token
     */
    public static function getToken(): Token;
}
