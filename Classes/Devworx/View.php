<?php

namespace Devworx;

use \Devworx\Interfaces\IView;

/**
 * The view class for rendering with variables and templates
 */
class View implements IView {
  
	/** @var string The id of the view */
	protected $id = '';
	/** @var string The fileName of the template */
	protected $fileName = '';
	/** @var array The variables to provide to the template */
	protected $variables = [];
	/** @var string The encoding of the template */
	protected $encoding = '';
	/** @var string The name of the renderer */
	protected $renderer = '';
	/** @var mixed The container for all provided variables */
	protected $all = false;
  
	function __construct(
		string $id='',
		string $fileName='',
		array $variables=null,
		string $renderer='',
		string $encoding=''
	){
		$this->id = $id;
		$this->fileName = $fileName;
		$this->variables = is_null($variables) ? [] : $variables;
		$this->renderer = empty($renderer) ? Frontend::getConfig('view','renderer') : $renderer;
		$this->encoding = $encoding;
	}
  
  /**
   * Getter for the id
   *
   * @return string
   */
  function getId(): string {
    return $this->id;
  }
  
  /**
   * Setter for the id
   *
   * @param string $id The value for the id
   * @return void
   */
  function setId(string $id): void {
    $this->id = $id;
  }
  
  /**
   * Getter for the renderer
   *
   * @return string
   */
  function getRenderer(): string {
    return $this->renderer;
  }
  
  /**
   * Setter for the renderer
   *
   * @param string $renderer The value for the renderer
   * @return void
   */
  function setRenderer(string $renderer): void {
    $this->renderer = $renderer;
  }
  
  /**
   * Getter for the fileName
   *
   * @return string
   */
  function getFile(): string {
    return $this->fileName;
  }
  
  /**
   * Setter for the template fileName
   *
   * @param string $fileName The value for the template fileName
   * @return void
   */
  function setFile(string $fileName): void {
    $this->fileName = $fileName;
  }
  
  /**
   * Getter for the 'all' flag
   *
   * @return bool
   */
  function getProvideAll(): bool {
    return $this->all;
  }
  
  /**
   * Setter for the 'all' flag
   *
   * @param bool $all The value for 'all'
   * @return void
   */
  function setProvideAll(bool $all=true): void {
    $this->all = $all;
  }
  
  /**
   * Getter for the encoding
   *
   * @return string
   */
  function getEncoding(): string {
    return $this->encoding;
  }
  
  /**
   * Setter for the encoding
   *
   * @param string $encoding The value for encoding
   * @return void
   */
  function setEncoding(string $encoding): void {
    $this->encoding = $encoding;
  }
  
  /**
   * Getter for the variables
   *
   * @return array
   */
  function getVariables(): array {
    return $this->variables;
  }
  
  /**
   * Setter for the variables
   *
   * @param array $variables
   * @return void
   */
  function setVariables(array $variables): void {
    $this->variables = $variables;
  }
  
  /**
   * Checks if a given variable name is set
   *
   * @param string $key The variable name
   * @return bool
   */
  function hasVariable(string $key): bool {
    return array_key_exists($key,$this->variables);
  }
  
  /**
   * Gets a variable from the internal variable buffer
   *
   * @param string $key The variable name
   * @return mixed|null
   */
  function getVariable(string $key){
    if( $this->hasVariable($key) )
      return $this->variables[$key];
    return null;
  }
  
  /**
   * Sets a variable in the internal variable buffer
   *
   * @param string $key The variable name
   * @param mixed $value The variable value
   * @return void
   */
  function setVariable(string $key,$value): void {
    $this->variables[$key] = $value;
  }
  
  /**
   * Sets a variable in the internal variable buffer using setVariable
   * Alias for usage like Extbase
   *
   * @param string $key The variable name
   * @param mixed $value The variable value
   * @return void
   */
  function assign(string $key,$value): void {
    $this->setVariable($key,$value);
  }
  
  /**
   * Sets multiple variables in the internal variable buffer using setVariable
   * Alias for usage like Extbase
   *
   * @param array $values The variable values
   * @return void
   */
  function assignMultiple(array $values): void {
    foreach( $values as $key => $value )
      $this->setVariable($key,$value);
  } 
  
  /**
   * Renders the current view using renderStatic
   *
   * @return \mixed
   */
  function render(){
    return self::renderStatic(
      $this->fileName,
      $this->variables,
      $this->renderer,
      $this->encoding
    );
  }
  
  /**
   * Converts the view to a string by rendering
   *
   * @return \mixed
   */
  function __toString(): string {
    return $this->render();
  }
  
  /**
   * Static render function for views
   *
   * @param string $fileName The template fileName
   * @param array $variables The variables for the template
   * @param string $renderer The renderer to use for the template
   * @param string $encoding The encoding for the template
   * @return \mixed
   */
  static function renderStatic(
    string $fileName,
    array $variables=null,
    string $renderer = '',
    string $encoding = ''
  ): \mixed {
    $result = null;
    if( is_file($fileName) ){
      ob_start();
      if( is_array($variables) && !empty($variables) )
        extract(
          $variables,
          EXTR_OVERWRITE
        );
      $result = include($fileName);
      if( is_numeric($result) )
        $result = ob_get_clean();
      else
        ob_end_flush();
      
	  if (class_exists($renderer)) {
		$instance = new $renderer();
		if (method_exists($instance, 'supports') && $instance->supports($result)) {
			return $instance->render($result, $variables, $encoding);
		}
	  }
	  return $result;
    }
    throw new \RuntimeException("Missing file {$fileName}");
    return $result;
  }
  
  /**
   * Static function for generating a View for layout rendering
   *
   * @param string $layout The template fileName
   * @param array $arguments The variables for the template
   * @param string $renderer The renderer to use for the template
   * @param string $encoding The encoding for the template
   * @return IView
   */
  static function Layout(string $layout,array $arguments=null,string $renderer='',string $encoding=''): IView {
    $layout = ucfirst($layout);
    $path = Frontend::path( 
      Frontend::getConfig('view','layoutRootPath'),
      "{$layout}.php"
    );
    $renderer = empty($renderer) ? Frontend::getConfig('view','renderer') : $renderer;
    return new View(
      $layout,
      $path,
      $arguments,
      $renderer,
      $encoding
    );
  }
  
  /**
   * Static function for generating a View for action template rendering
   *
   * @param string $controller The controller name
   * @param string $action The action name
   * @param array $arguments The variables for the template
   * @param string $renderer The renderer to use for the template
   * @param string $encoding The encoding for the template
   * @return IView
   */
  static function Template(string $controller,string $action,array $arguments=null,string $renderer='',string $encoding=''): IView {
    $controller = ucfirst($controller);
    $action = ucfirst($action);
    $path = Frontend::path( 
      Frontend::getConfig('view','templateRootPath'), 
      $controller, 
      "{$action}.php" 
    );
    $renderer = empty($renderer) ? Frontend::getConfig('view','renderer') : $renderer;
    return new View(
      "{$controller}-{$action}",
      $path,
      $arguments,
      $renderer,
      $encoding
    );
  }
  
  /**
   * Static function for generating a View for partial rendering
   *
   * @param string $partial The partial name
   * @param array $arguments The variables for the template
   * @param string $renderer The renderer to use for the template
   * @param string $encoding The encoding for the template
   * @return IView
   */
  static function Partial(string $partial,array $arguments=null,string $renderer='',string $encoding=''): IView {
    $partial = ucfirst($partial);
    $path = Frontend::path( 
      Frontend::getConfig('view','partialRootPath'), 
      "{$partial}.php" 
    );
    $renderer = empty($renderer) ? Frontend::getConfig('view','renderer') : $renderer;
    return new View(
      $partial,
      $path,
      $arguments,
      $renderer,
      $encoding
    );
  }
    
}

?>
