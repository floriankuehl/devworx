<?php

namespace Devworx\Controller;

use Devworx\Interfaces\IController;
use Devworx\Interfaces\IRequest;
use Devworx\Interfaces\IView;

use Devworx\Utility\PathUtility;

use Devworx\Configuration;
use Devworx\View;
use Devworx\Request;
use Devworx\Redirect;

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
	/** @var bool $blockLayout Flag to block layout rendering (template only) */
	protected $blockLayout = false;
  
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
	 * Sets the block rendering flag
	 *
	 * @return void
	 */
	function setBlockRendering(bool $value=true): void {
		$this->blockRendering = $value;
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
	 * Sets the block layout rendering flag
	 *
	 * @return void
	 */
	function setBlockLayout(bool $value=true): void {
		$this->blockLayout = $value;
	}
	
	/** 
	 * Returns the block layout rendering flag
	 *
	 * @return bool
	 */
	function getBlockLayout(): bool {
		return $this->blockLayout;
	}
	
	/** 
	 * Redirects to a specific controller action
	 *
	 * @return void
	 */
	function redirect(
		string $action, 
		string $controller=null, 
		array $arguments=null, 
		string $anchor=null, 
		int $status=301 
	): void {
		Redirect::action( 
			$controller ?? $this->getId(),
			$action,
			$arguments,
			$anchor,
			$status
		);
	}

	/** 
	 * The initialize function
	 *
	 * @return void
	 */
	abstract function initialize(): void;

	/** 
	 * This function processes actions by retrieving the template and rendering the view
	 *
	 * @param string action The name of the action
	 * @param array $arguments The arguments for the action
	 * @return mixed
	 */
	function processAction(string $action,...$arguments): mixed {
		$controller = $this->getId();
		$action = ucfirst($action);
		$this->view->setId("{$controller}-{$action}");
		$file = PathUtility::currentConfig(['view','templateRootPath'], $controller, "{$action}.php");
		$this->view->setFile($file);
		$encoding = Configuration::get('view','encoding');
		if( is_string($encoding) ) $this->view->setEncoding($encoding);
		$this->view->setProvideAll( Configuration::get('doctype') === 'html' );
		$this->view->setVariables([]);
		$response = call_user_func([$this,"{$action}Action"],...$arguments);
		return $this->view->render($response);
	}
}

?>
