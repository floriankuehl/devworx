<?php

namespace Cascade\Runtime;

class ViewHelperInvoker
{
	public static function namespaces(Context|array $context): array {
		return is_array($context) ? 
			( $context['namespaces'] ?? [] ) : 
			$context->get('namespaces',[]);
	}
		
	public static function defaultNamespace(Context|array $context): string {
		$fallback = \Devworx\Devworx::framework() . "\\ViewHelper";
		return is_array($context) ? 
			( $context['defaultNamespace'] ?? $fallback ) : 
			$context->get('defaultNamespace',$fallback);
	}
	
    public static function invoke(Context|array $context, string $name, array $arguments = [], $input = null): mixed
    {
		$className = self::resolveClassName($context, $name);
        if (!class_exists($className)) {
            throw new \RuntimeException("ViewHelper '{$name}' not found as class '{$className}'");
        }
		
		$helper = new $className();
        if (!method_exists($helper, 'render')) {
            throw new \RuntimeException("ViewHelper '{$className}' does not implement a render() method.");
        }
		$helper->initializeArguments();

        return $helper->render($context,$arguments, $input);
    }

    protected static function resolveClassName(Context|array $context, string $name): string
    {
		$namespaces = self::namespaces($context);
		$defaultNamespace = self::defaultNamespace($context);
		
        if (str_contains($name, ':')) {
            [$nsAlias, $helperPath] = explode(':', $name, 2);
            $helperParts = explode('.', $helperPath);
            $classBase = implode('\\', array_map('ucfirst', $helperParts)) . 'ViewHelper';

            if (isset($namespaces[$nsAlias])) {
                return rtrim($namespaces[$nsAlias], '\\') . '\\' . $classBase;
            }

            throw new \RuntimeException("Namespace alias '{$nsAlias}' is not registered for '{$classBase}'.");
        }

        $parts = explode('.', $name);
        $class = implode('\\', array_map('ucfirst', $parts)) . 'ViewHelper';

        return rtrim($defaultNamespace, '\\') . '\\' . $class;
    }
}
