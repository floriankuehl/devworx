<?php

namespace Devworx\Renderer;

use Devworx\Frontend;
use Devworx\Utility\ArrayUtility;

interface IRenderer {
  static function render($source,array $variables,string $encoding);
}

abstract class AbstractRenderer implements IRenderer {
  
  abstract static function render($source,array $variables,string $encoding);
  
}

?>