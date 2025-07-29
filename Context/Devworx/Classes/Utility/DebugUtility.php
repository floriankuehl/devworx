<?php

namespace Devworx\Utility;

use \Devworx\Devworx;

class DebugUtility {
  
	public static $errors = [];
	public static $exceptions = [];
	
	const STYLE = 'debug.css';
	const ERROR_TITLE = 'Ooops!';
	
	/**
	 * Generates the stylesheet url to 
	 *
	 * @param bool $cached allows cached url, otherwise appends filemtime
	 * @return string $result The path to the stylesheet file
	 */
	public static function styleUrl(bool $cached=true): string {
		$url = PathUtility::resource( 
			Devworx::framework(), 
			'Styles', 
			self::STYLE 
		);
		return $cached ? $url : $url . '?v='.filemtime($url);
	}

	/**
	 * Retrieves the callee from the backtrace
	 *
	 * @return array $result class, function and line
	 */
	public static function callee(): array {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1];
		return [
			$trace['class'] ?? '',
			$trace['function'] ?? '',
			$trace['line'] ?? 0
		];
	}
	
	/** 
	 * Dumps a variable for debugging and adds a debugger wrap
	 * 
	 * @param mixed $var The variable to dump
	 * @param string $title The optional title for the debugger wrap
	 * @param string $method The optional method name for the debugger wrap
	 * @param int $line The optional line number for the debugger
	 * @return string
	 */
	static function var_dump(
		mixed $var,
		string $title='',
		string $method='',
		int $line=0
	): string {
		
		ob_start();
		var_dump($var);
		$result = ob_get_clean();
		
		if( empty($title) ) {
			[$title,$method,$line] = self::callee();
		}
		
		return self::renderDump(
			htmlentities($result),
			$title,
			$method,
			$line
		);
	}

	/** 
	 * Exception handler for devworx with backtrace
	 * 
	 * @param \Throwable $e The exception
	 * @return void
	 */
	static function exception(\Throwable $e): void {
		$exception = [
			$e->getMessage(),
			$e::class,
			$e->getFile(),
			$e->getLine(),
			$e->getTrace()
		];
		if( Devworx::debug() ){
			echo self::renderException(...$exception);
		}
		self::$exceptions[] = $exception;
	}
	
	/** 
	 * Error handler for devworx
	 * 
	 * @param int $errno the error number
	 * @param string $errstr the error message
	 * @param string $errfile the file in which the error occured
	 * @param int $errline the line of the file
	 * @return void
	 */
	static function error(
		int $errno,
		string $errstr,
		string $errfile = '',
		int $errline = 0
	): bool {
		
		$error = [
			self::ERROR_TITLE,
			$errno,
			$errstr,
			empty($errfile) ? $_SERVER['SCRIPT'] : $errfile,
			$errline
		];
		
		if( Devworx::debug() ){
			echo self::renderError(...$error);
		}
		
		self::$errors[] = $error;
		return true;
	}
	
	/** 
	 * Renders the frame html for the debugger
	 * 
	 * @param string $header the main header content
	 * @param string $info informations below the header
	 * @param string $content body content
	 * @param bool $stylesheet Defines wether or not to include the stylesheet
	 * @return string
	 */
	public static function renderDebugger(
		string $header,
		string $info,
		string $content,
		bool $stylesheet=true
	): string {
		$style = self::styleUrl();
		$context = Devworx::context();
		$framework = Devworx::framework();
		
		return implode(PHP_EOL,[
			( $stylesheet ? "<style>@import url('{$style}');</style>" : '' ),
			"<devworx-debug title=\"{$header}\" class=\"d-flex flex-column debugger text-bg-dark\">",
				"<header>{$header}</header>",
				"<small>[framework:{$framework},context:{$context}]</small>",
				"<small>{$info}</small>",
				$content,
			"</devworx-debug>"
		]);
	}
	
	/** 
	 * Renders a dump to html
	 * 
	 * @param string $content The dumped variable content
	 * @param string $title The optional title for the debugger wrap
	 * @param string $method The optional method name for the debugger wrap
	 * @param int $line The optional line number for the debugger
	 * @param bool $stylesheet Defines wether or not to include the stylesheet
	 * @return string
	 */
	public static function renderDump(
		string $content,
		string $header='',
		string $method='',
		int $line=0,
		bool $stylesheet = true
	): string {
		return self::renderDebugger(
			$header,
			"{$method} on line {$line}",
			"<pre>{$content}</pre>",
			$stylesheet
		);
	}
	
	/** 
	 * Renders a exception to html
	 * 
	 * @param string $title The optional title for the debugger wrap
	 * @param string $type The class of the exception
	 * @param string $file The file in which the exception occured
	 * @param int $line The optional line number for the debugger
	 * @param array $trace The backtrace
	 * @param bool $stylesheet Defines wether or not to include the stylesheet
	 * @return string
	 */
	public static function renderException(
		string $title,
		string $type,
		string $file,
		int $line=0,
		array $trace=[],
		bool $stylesheet=true
	): string {
		foreach( $trace as $i => $row ){
			$trace[$i] = self::renderTrace(
				$i,
				$row['class'] ?? '',
				$row['type'] ?? '',
				$row['function'] ?? '',
				$row['args'] ?? [],
				$row['line'] ?? 0
			);
		}
		
		$trace = implode(PHP_EOL,$trace);
		
		return self::renderDebugger(
			"{$type}: {$title}",
			"{$file} on line {$line}",
			"<div data-type=\"trace\">{$trace}</div>",
			$stylesheet
		);
	}
	
	/** 
	 * Renders a trace entry
	 * 
	 * @param int $index The trace index
	 * @param string $class The class of the trace
	 * @param string $type The type of the trace
	 * @param string $function The function name
	 * @param array $args The function arguments
	 * @param int $line The optional line number for the debugger
	 * @return string
	 */
	public static function renderTrace(
		int $index,
		string $class='',
		string $type='',
		string $function='',
		array $args=null,
		int $line=0
	): string {
		$args = !empty($args) ? json_encode($args,JSON_PRETTY_PRINT) : '';
		return implode(PHP_EOL,[
			"<div data-type=\"tracerow\">",
				"<div>",
					"<span data-type=\"index\">{$index}</span>",
					( empty($class) ? $class : "<span data-type=\"class\">{$class}</span>" ),
					( empty($type) ? $type : "<span data-type=\"type\">{$type}</span>" ),
					( empty($function) ? $function : "<span data-type=\"function\">{$function}</span>" ),
					( empty($line) ? '' : "<span data-type=\"line\">{$line}</span>" ),
				"</div>",
				( empty($args) ? $args : "<pre>".htmlentities($args)."</pre>" ),
			"</div>"
		]);
	}
	
	/** 
	 * Renders a error message
	 * 
	 * @param string $title a custom title for the message
	 * @param int $number the error number
	 * @param string $message the error message
	 * @param string $file The file in which the error occured
	 * @param int $line The line number for the debugger
	 * @return string
	 */
	public static function renderError(
		string $title,
		int $number,
		string $message,
		string $file='',
		int $line=0,
		bool $stylesheet=true
	): string {
		
		$trace = debug_backtrace(false);
		$index = 0;
		$trace = implode(PHP_EOL,array_map(
			fn($row)=>self::renderTrace(
				$index++,
				$row['class'] ?? '',
				$row['type'] ?? '',
				$row['function'] ?? '',
				$row['args'] ?? [],
				$row['line'] ?? 0
			),
			array_slice($trace,2),
		));
		
		return self::renderDebugger(
			$message,
			"Code {$number} in {$file} on line {$line}",
			$trace,
			$stylesheet
		);
	}
	
}
