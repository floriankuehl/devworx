<?php

namespace Devworx\Renderer;

use Devworx\Frontend;
use Devworx\Utility\ArrayUtility;


/**
 * Interface IRenderer
 * Basic interface for every renderer
 */
interface IRenderer {
  static function render($source,array $variables,string $encoding): void;
}

abstract class AbstractRenderer implements IRenderer {
 
  /**
   * Function render
   * Abstract function for rendering anything
   * 
   * @return void
   */
  abstract static function render($source,array $variables,string $encoding): void;
  
}

?>
