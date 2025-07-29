<?php

namespace Cascade\Interfaces;

interface IValueNode extends INode
{
    /**
     * Gibt den primitiven oder logischen Typ des Wertes zurück,
     * z. B. 'string', 'number', 'array', 'object', etc.
     *
     * @return string
     */
    public static function getValueType(): string;
}
