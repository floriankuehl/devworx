<?php

namespace Devworx\Renderer;

use Devworx\Devworx;
use Devworx\Configuration;
use Devworx\Interfaces\IParser;

class RenderContext {
	
	public const TEMPLATE = 'template';
	public const PARTIAL = 'partial';
	public const LAYOUT = 'layout';

	public ?IParser $parser = null;
    public string $type; // 'layout', 'template', 'partial'
	public array $variables = [];
	    
	public string $hash = '';
	public string $context = '';
	public string $controller = '';
	public string $action = '';
	
    public function __construct(
		?IParser $parser = null,
		string $type = 'template', 
		array $vars = []
	) {
		$this->parser = $parser;
        $this->type = $type;
        $this->variables = $vars;
		$this->hash = sha1(json_encode($vars));
		
		$this->context = Devworx::context();
		$this->controller = Configuration::get('context','controller');
		$this->action = Configuration::get('context','action');
    }
	
	public function getIdentifier(): string {
		$tokens = array_filter(
			[
				$this->type,
				$this->controller,
				$this->action,
				$this->hash
			],
			fn($value) => ( isset($value) && !empty($value) )
		);
        return implode('_',$tokens);
    }
}