<?php

namespace Cascade\Interfaces;

use Cascade\Interfaces\INode;

interface ITokenPattern {
    public static function matches(array $tokens): bool;
    public static function fromTokens(array $tokens, int &$i=0): ?INode;
    public const PATTERN = [];
}