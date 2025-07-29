<?php

namespace Cascade\Runtime;

class Functions
{
    /**
     * Führt eine Funktion oder Methode dynamisch aus.
     *
     
     * @param string $name Funktions- oder Methodennamen
     * @param array $arguments Benannte oder numerische Argumente
	 * @param mixed $input Eingabewert
     * @return mixed
     * @throws \RuntimeException
     */
    public static function call(string $name, array $arguments = [], mixed $input = null)
    {
        // Static function call (e.g. ClassName::method)
        if (strpos($name, '::') !== false) {
            [$class, $method] = explode('::', $name);
            if (!method_exists($class, $method)) {
                throw new \RuntimeException("Static method {$class}::{$method} not found");
            }
            return $class::$method(...array_values($arguments));
        }

        // Built-in PHP function
        if (function_exists($name)) {
			if( $input === null )
				return $name(...array_values($arguments));
			return $name($input,...array_values($arguments));
        }

		/*
        // Object method call
        if (is_object($input) && method_exists($input, $name)) {
            return $input->$name(...array_values($arguments));
        }
		*/

        throw new \RuntimeException("Function or method '{$name}' not found.");
    }
}
