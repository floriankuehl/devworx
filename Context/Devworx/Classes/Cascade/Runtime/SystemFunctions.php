<?php

namespace Cascade\Runtime;

class SystemFunctions
{
	public static $systemMethods = [
		'if' => 'ifFunction',
		'in' => 'inFunction',
		// weitere Methoden hier ...
	];
	
    public static function call(string $functionName, array $arguments)
	{
		// Mapping von Systemfunktionsnamen zu internen Methoden

		if (isset(self::$systemMethods[$functionName])) {
			$method = self::$systemMethods[$functionName];
			return forward_static_call_array([self::class, $method], $arguments);
		}

		throw new \RuntimeException("Unbekannte Systemfunktion: {$functionName}");
	}
	
	public static function evaluateBinaryOperator(string $operator, $left, $right)
    {
        switch ($operator) {
            case '+':
                return $left + $right;
            case '-':
                return $left - $right;
            case '*':
                return $left * $right;
            case '/':
                if ($right == 0) {
                    throw new \RuntimeException("Division durch Null");
                }
                return $left / $right;
            case '**':
                return pow($left,$right);
            case '==':
                return $left == $right;
            case '!=':
                return $left != $right;
            case '>':
                return $left > $right;
            case '>=':
                return $left >= $right;
            case '<':
                return $left < $right;
            case '<=':
                return $left <= $right;
            default: {
				if( array_key_exists($operator,self::$systemMethods) ){
					return forward_static_call_array(
						[self::class, self::$systemMethods[$operator]],
						[$left,$right]
					);
				}
				throw new \RuntimeException("Unbekannter Operator: {$operator}");
			}
        }
    }

	protected static function ifFunction(array $args)
	{
		$condition = $args['condition'] ?? false;
		$then = $args['then'] ?? null;
		$else = $args['else'] ?? null;
		return $condition ? $then : $else;
	}
	
    /**
     * Prüft, ob $needle in $haystack enthalten ist.
     *
     * @param mixed $needle
     * @param mixed $haystack
     * @return bool
     */
    public static function inFunction(mixed $left,mixed $right): bool
    {
		if (is_array($right)) {
            if (is_array($left)) {
                // Prüfen ob $left ein Subset von $right ist
                return empty(array_diff($left, $right));
            }
            return in_array($left, $right, true);
        }

        if (is_string($right)) {
            if (is_string($left) || is_numeric($left)) {
                return strpos($right, (string)$left) !== false;
            }
        }

        if (is_object($right)) {
            if (is_string($left)) {
                return property_exists($right, $left);
            }
        }

        return false;
    }
}
