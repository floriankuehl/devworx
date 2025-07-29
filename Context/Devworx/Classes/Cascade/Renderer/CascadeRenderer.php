<?php

namespace Cascade\Renderer;

use Devworx\Renderer\AbstractRenderer;
use Devworx\Renderer\RenderContext;
use Devworx\Interfaces\IParser;

use Devworx\Utility\DebugUtility;
use Devworx\Devworx;
use Devworx\Configuration;
use Devworx\Caches;

use Cascade\Parser\TemplateParser;
use Cascade\Runtime\Context;
use Cascade\Cache\CascadeCache;
use Cascade\Node\RootNode;

class CascadeRenderer extends AbstractRenderer 
{
	protected ?Context $context = null;	
	protected ?TemplateParser $parser = null;	
	protected ?CascadeCache $cache = null;
	
	public function __construct(){
		$this->context = new Context();
		$this->context->set('namespaces',Configuration::get('namespaces'));
		$this->parser = new TemplateParser();
		$this->cache = Caches::get('Cascade');
	}
	
	/**
	 * Returns the current variable context
	 *
	 * @return Context
	 */
	public function createContext(?array $variables=null): Context {
		$this->context->setAll($variables);
		return $this->context;
	}
  
	/**
	 * Checks if the template is supported
	 *
	 * @param mixed $template the template to check
	 * @return bool
	 */
	public function supports(mixed $template): bool {
		return is_string($template);
	}
  
	/**
	 * 
	 *
	 * @param mixed $template the template to check
	 * @param array $variables the template context variables
	 * @param mixed $contextVar the name of the context variable
	 * @return mixed
	 */
	public function parseAndCompile(mixed $template,string $contextVar='$context'): mixed {
		$parsed = $this->parser->parse($template);
		//echo DebugUtility::var_dump($parsed,__CLASS__,__METHOD__,__LINE__);
		return '(function()use(' . $contextVar . '){' . PHP_EOL . 'return ' . $parsed->compile($contextVar) . ';'.PHP_EOL.' })()';
	}
	
	/**
	 * 
	 *
	 * @param mixed $template the template to check
	 * @param array $variables the template context variables
	 * @param mixed $contextVar the name of the context variable
	 * @return mixed
	 */
	public function parseAndEvaluate(mixed $template,array $variables): mixed {
		$parsed = $this->parser->parse($template);
		// echo DebugUtility::var_dump($parsed,__CLASS__,__METHOD__,__LINE__);
		foreach( $variables as $name => $value )
			$this->context->set($name,$value);
		
		return $parsed->evaluate($this->context);
	}
  	
	public function prepare(string $template, RenderContext $renderContext): string
	{
		foreach ($renderContext->variables as $name => $value) {
			$this->context->set($name, $value);
		}
		
		$file = $this->cache->file(Devworx::context(),$renderContext);
		if( file_exists($file) ) 
			return file_get_contents($file);
		
		if( $this->cache->create(Devworx::context(),$renderContext, $template) )
			return file_get_contents($file);

		return $template;
	}
	
	public function prepareFile(string $fileName, RenderContext $renderContext): string
	{
		$this->context->setAll($renderContext->variables);
		
		$file = $this->cache->file(Devworx::context(),$renderContext);
		if( file_exists($file) ) return file_get_contents($file);
		
		ob_start();
		$context = $this->context;
		$template = include($fileName);
		if( is_numeric($template) )
			$template = ob_get_clean();
		else
			ob_end_flush();
		
		$root = $renderContext->parser->parse($template);
		$template = $root->compile('$context','null');

		/*
		if( $renderContext->type === 'layout' ){
			echo \Devworx\Utility\DebugUtility::var_dump($template);
		}
		*/

		if( $this->cache->create(Devworx::context(), $renderContext, $template) )
			return file_get_contents($file);
			
		return $template;
	}
	
	/**
	 * Renders a source string template using branched object access
	 *
	 * @param mixed $template The given source template text
	 * @param array $variables The provided variables for this renderer
	 * @param string $contextType The provided render context name
	 * @param string $encoding The standard encoding for this renderer
	 * @return string
	 */
	public function render(mixed $template,array $variables,string $contextType='',string $encoding=''): string {
		
		$contextType = empty($contextType) ? 
			RenderContext::TEMPLATE : 
			$contextType;
		
		return $this->renderWithContext(
			$template,
			$contextType, 
			$variables
		);
	}
	
	/**
	 * Renders a source string template using branched object access
	 *
	 * @param string $template A given source file
	 * @param array $variables The provided variables for this renderer
	 * @param string $contextType The provided render context name
	 * @param string $encoding The standard encoding for this renderer
	 * @return string
	 */
	public function renderFile(string $fileName,array $variables,string $contextType='',string $encoding=''): string {
		
		$contextType = empty($contextType) ? 
			RenderContext::TEMPLATE : 
			$contextType;
		
		return $this->renderFileWithContext(
			$fileName,
			$contextType, 
			$variables
		);
	}
	
	public function renderWithContext(string $template, string $contextType, array $variables): string
	{
		$context = $this->context;
		return eval(
			implode('',[ 
				'return ', 
				$this->prepare( 
					$template, 
					new RenderContext(
						$this->parser,
						$contextType,
						$variables
					)
				), 
				';' 
			])
		);
	}
	
	public function renderFileWithContext(string $fileName, string $contextType, array $variables): string
	{
		$context = $this->context;
		return eval(
			implode('',[ 
				'return ', 
				$this->prepareFile( 
					$fileName, 
					new RenderContext(
						$this->parser,
						$contextType,
						$variables
					)
				), 
				';' 
			])
		);
	}
}

?>
