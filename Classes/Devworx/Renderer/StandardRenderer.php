<?php

namespace Devworx\Renderer;

use \Devworx\Utility\DebugUtility;

class StandardRenderer extends AbstractRenderer {
  
  public static function render($source,array $variables,string $encoding=''){
    return $source;
  }
}

?>
