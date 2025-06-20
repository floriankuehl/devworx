<?php

namespace Devworx\Renderer;

use \Devworx\Utility\ArrayUtility;

class FluidRenderer extends AbstractRenderer {
  
	const PIPECHAR = '->';
  
	public static function Value(mixed $branch,mixed $property,bool $enableMagicCalls=false): mixed {
		
		if( $branch === null )
			return $branch;
		
		if( is_numeric($branch) )
			return $branch;
		
		if( $property === null )
			return $property;
		
		if( is_object($property) )
			return null;
		
		if( is_string($branch) ){
			if( is_numeric($property) )
				return $branch[$property] ?? null;
			if( is_string($property) )
				return strpos($branch,$property);
			if( is_callable($property) )
				return call_user_func($property,$branch);
			return $branch;
		}
		
		if( is_array($branch) ){
			if( is_array($property) )
				return array_filter($branch,fn($p,$index)=>in_array($index,$property));
			if( is_callable($property) )
				return call_user_func($property,$branch);
			
			return $branch[$property] ?? null;
		}
		
		if( is_numeric($property) )
				return null;
		
		if( is_object($branch) ){
			if( is_callable($property) )
				return call_user_func($property,$branch);
			
			if( is_array($property) )
				return null;
			
			if( is_string($property) ){
			
				if( property_exists($branch,$property) )
					return $branch->$property;
				
				if( method_exists($branch,$property) ){
					if( ( new \ReflectionMethod($branch, $property) )->getNumberOfRequiredParameters() === 0 )
						return call_user_func([$branch,$property]);
				}
				
				if( $enableMagicCalls && method_exists($branch,'__call' ) )
					return $branch->$property();
				
				$name = 'get' . ucfirst($property);
				if( method_exists($branch,$name) )
					return call_user_func([$branch,$name]);
			}
		}
		
		if( is_callable($branch) )
			return self::Value( 
				call_user_func($branch,$property), 
				$property,
				$enableMagicCalls
			);
		
		return null;
	}
  
	/**
	 * Parses a branch in the object tree
	 *
	 * @param string $key The key to read
	 * @param mixed $branch The current branch
	 * @return mixed
	 */
	public static function parseBranch(string $key,mixed $branch,bool $enableMagicCalls=false){

		if (empty($key) || is_null($branch)) return '';

		$segments = explode('.', $key);
		$first = array_shift($segments);

		if (empty($first)) 
			return $branch;

		if (empty($segments)) 
			return self::Value($branch,$first,$enableMagicCalls) ?? "{{$key}}";

		$rest = implode('.', $segments);
		if (is_object($branch))
			return self::parseBranch($rest, call_user_func([$branch, "get" . ucfirst($first)]));
		
		if (is_array($branch)) {
			if (array_key_exists($key, $branch)) 
				return $branch[$key];
			if (array_key_exists($first, $branch)) 
				return self::parseBranch($rest, $branch[$first]);
			return "{{$first}.{$rest}}";
		}
		
		if (is_numeric($branch)) 
			return $branch;
		if (is_string($branch) && isset($branch[$first])) 
			return self::parseBranch($rest, $branch[$first]);

		return $branch;
	}

	/**
	 * Extracts all variables from a given source string
	 *
	 * @param string $source The source text
	 * @return array|null
	 */
	public static function extractVariables(string $source): ?array {
		$matches = [];
		$found = preg_match_all('~\{([^\{\}]+)\}~', $source, $matches, PREG_SET_ORDER);
		return $found === false ? null : array_column($matches, 1);
	}

	/**
	 * Converts a given value to a string
	 *
	 * @param mixed $value The given value
	 * @param string $key A possible subkey of the value (not used)
	 * @return string
	 */
	public static function stringify( mixed $value, string $key='' ): string {
		if (is_null($value)) return 'null';
		if($value instanceof \DateTime) return $value->format('Y-m-d\\TH:i:s');
		if (is_bool($value)) return $value ? '1' : '0';
		return (string) $value;
	}

	/**
	 * Checks if the template is supported
	 *
	 * @param mixed $template the template to check
	 * @return bool
	 */
	public function supports(mixed $template): bool {
		return is_string($template) && str_contains($template, '{');
	}
  
	/**
	 * Resolves viewhelper classes
	 *
	 * @param mixed $identifier the view helper call
	 * @return ?callable $result the reference to the render call
	 */
	protected function resolveViewHelper(string $identifier): ?callable {
        [$prefix, $name] = explode(':', $identifier);
        $segments = explode('.', $name);
        $class = ucfirst(array_shift($segments));
        $method = implode('', array_map('ucfirst', $segments));
        $fqcn = ($this->viewHelperNamespaces[$prefix] ?? null) . "\\" . $class . 'ViewHelper';

        if (class_exists($fqcn) && method_exists($fqcn, 'render')) {
            return [$fqcn, 'render'];
        }
        return null;
    }

	/**
	 * Evaluates expressions
	 *
	 * @param mixed $expr the expression
	 * @param array $variables the variables for the expression
	 * @return mixed $result the evaluated result
	 */
    protected function evaluateExpression(string $expr, array $variables): mixed {
        // Piping support (e.g., user.name -> f:format.upper)
        $parts = array_map('trim', explode( self::PIPECHAR, $expr));
        $value = self::parseBranch(array_shift($parts), $variables);

        foreach ($parts as $pipe) {
            $callable = $this->resolveViewHelper($pipe);
            if (is_callable($callable)) {
                $value = call_user_func($callable, ['value' => $value]);
            }
        }
        return $value;
    }
  
	/**
	 * Renders a source string template using branched object access
	 *
	 * @param mixed $source The given source template text
	 * @param array $variables The provided variables for this renderer
	 * @param string $encoding The standard encoding for this renderer
	 * @return string
	 */
	public function render(mixed $template,array $variables,string $encoding=''): string {
		if (is_string($template) && str_contains($template, '{')) {
			$keys = self::extractVariables($template);
			if (is_null($keys)) return $template;

			$enableMagicCalls = ArrayUtility::key($this->options, 'enableMagicCalls', false);
			$values = [];

			foreach ($keys as $key) {
				$value = $this->evaluateExpression($key, $variables);
				$values["{{$key}}"] = self::stringify($value, $key);
			}

			return str_replace(array_keys($values), array_values($values), $template);
		}
		return (string) $template;
	}
}

?>
