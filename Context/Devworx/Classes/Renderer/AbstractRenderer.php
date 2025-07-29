<?php

namespace Devworx\Renderer;

use \Devworx\Interfaces\IRenderer;
use \Devworx\Frontend;
use \Devworx\Utility\ArrayUtility;

abstract class AbstractRenderer implements IRenderer {
 
   protected $options = [];
 
  /**
   * Function render
   * Abstract function for rendering anything
   * 
   * @param mixed $template The given template
   * @param array $variables 
   * @param string $renderContext
   * @param string $encoding
   * @return mixed
   */
  abstract function render(mixed $template,array $variables,string $renderContext,string $encoding): mixed;
  
  public function setOptions(array $options): void {
	  $this->options = $options;
  }
  
  abstract function supports(mixed $template): bool;
}

?>
