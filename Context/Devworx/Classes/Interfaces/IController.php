<?php

namespace Devworx\Interfaces;

/**
 * Interface for basic controllers
 */
interface IController {

	/**
	 * Returns the id of the controller
	 * 
	 * @return string
	 */
	function getId(): string;
	
	/**
	 * Returns the namespace of the controller class
	 *
	 * @return string
	 */
	function getNamespace(): string;
	
	/**
	 * Returns the blockRendering flag
	 *
	 * @return bool
	 */
	function getBlockRendering(): bool;
	
	/**
	 * Sets the blockRendering flag
	 *
	 * @param bool $value
	 * @return void
	 */
	function setBlockRendering(bool $value=true): void;
	
	/**
	 * Returns the blockLayout flag
	 *
	 * @return bool
	 */
	function getBlockLayout(): bool;
	
	/**
	 * Sets the blockLayout flag
	 *
	 * @param bool $value
	 * @return void
	 */
	function setBlockLayout(bool $value=true): void;
	
	/**
	 * Returns the current request
	 *
	 * @return IRequest
	 */
	function getRequest(): IRequest;
	
	/**
	 * Returns the current view
	 *
	 * @return IView
	 */
	function getView(): IView;

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
	): void;

	/**
	 * Iniailizes the controller
	 *
	 * @return void
	 */
	function initialize(): void;
	
	/**
	 * Processes an action
	 *
	 * @param string $action The action name
	 * @param array $arguments The action arguments
	 * @return mixed
	 */
	function processAction(string $action,...$arguments): mixed;
}

?>