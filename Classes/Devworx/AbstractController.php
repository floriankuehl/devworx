<?php

namespace Devworx;

use \Devworx\Utility\StringUtility;
use \Devworx\Utility\ModelUtility;
use \Devworx\Utility\CookieUtility;

/**
 * Interface for basic controllers
 */
interface IController {
  function getId(): string;
  function getNamespace(): string;
  function getBlockRendering(): bool;
  function getRequest(): IRequest;
  function getView(): IView;
  
  function initialize();
  function processAction(string $action,...$arguments);
}

/**
 * The base class for controllers
 */
abstract class AbstractController implements IController {
  
	/** @var string $id The id of the controller */
	protected $id = '';
	/** @var string $namespace The namespace of the controller */
	protected $namespace = '';
	/** @var IView $view A interface reference to the view of the controller */
    protected $view = null;
	/** @var IRequest $request A interface reference to the current request */
    protected $request = null;
	/** @var bool $blockRendering Flag to block rendering for functional actions */
    protected $blockRendering = false;
  
	public function __construct(){
		$tokens = explode("\\",get_called_class());
		$this->id = str_replace("Controller","",array_pop($tokens));
		$this->namespace = implode("\\",$tokens);
		$this->view = new View($this->id);
		$this->request = new Request();
	}

	/** 
	 * Returns the id of the controller
	 *
	 * @return string
	 */
	function getId(): string {
		return $this->id;
	}

	/** 
	 * Returns the namespace of the controller
	 *
	 * @return string
	 */
	function getNamespace(): string {
		return $this->namespace;
	}

	/** 
	 * Returns the request interface
	 *
	 * @return IRequest
	 */
	function getRequest(): IRequest {
		return $this->request;
	}

	/** 
	 * Returns the view interface
	 *
	 * @return IView
	 */
	function getView(): IView {
		return $this->view;
	}

	/** 
	 * Returns the block rendering flag
	 *
	 * @return bool
	 */
	function getBlockRendering(): bool {
		return $this->blockRendering;
	}

	/** 
	 * The initialize function
	 *
	 * @return void
	 */
	abstract function initialize();

	/** 
	 * This function processes actions by retrieving the template and rendering the view
	 *
	 * @param string action The name of the action
	 * @param array $arguments The arguments for the action
	 * @return mixed
	 */
	function processAction(string $action,...$arguments){
		$controller = $this->getId();
		$action = ucfirst($action);
		$this->view->setId("{$controller}-{$action}");
		$file = Frontend::path( Frontend::getConfig('view','templateRootPath'), $controller, "{$action}.php" );

		$this->view->setFile($file);
		$encoding = Frontend::getConfig('view','encoding');
		if( is_string($encoding) ) $this->view->setEncoding($encoding);
		$this->view->setProvideAll( Frontend::getConfig('doctype') === 'html' );
		$this->view->setVariables([]);
		$response = call_user_func([$this,"{$action}Action"],...$arguments);

		return $this->view->render($response);
	}
}

?>
