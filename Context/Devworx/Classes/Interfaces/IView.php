<?php

namespace Devworx\Interfaces;

/**
 * The interface for general Views
 */
interface IView {
  
  /**
   * Gets the id of the view
   *
   * @return string $id
   */
  function getId(): string;
  
  /**
   * Sets the id of the view
   *
   * @param string $id
   * @return void
   */
  function setId(string $id): void;
  
  /**
   * Gets the file name of the template
   *
   * @return string $fileName
   */
  function getFile(): string;
  
  /**
   * Sets the fileName of the template
   *
   * @param string $fileName
   * @return void
   */
  function setFile(string $fileName): void;
  
  /**
   * Gets the provideAll flag
   *
   * Provides a variable container for all available variables
   * @return bool
   */
  function getProvideAll(): bool;
  
  /**
   * Sets the provideAll flag
   *
   * @param bool $all
   * @return void
   */
  function setProvideAll(bool $all): void;
  
  /**
   * Gets the template enconding function name
   *
   * @return string
   */
  function getEncoding(): string;
  
  /**
   * Sets the template enconding function name
   *
   * @param string $encoding
   * @return void
   */
  function setEncoding(string $encoding): void;
  
  /**
   * Gets the provided variables
   *
   * @return array
   */
  function getVariables(): array;
  
  /**
   * Sets the provided variables
   *
   * @param array $variables
   * @return void
   */
  function setVariables(array $variables): void;
  
  /**
   * Gets the renderer FQCN
   *
   * @return string
   */
  function getRenderer(): string;
  
  /**
   * Sets the renderer FQCN
   *
   * @param string $renderer
   * @return void
   */
  function setRenderer(string $renderer): void;
  
  /**
   * Checks for a provided variable 
   *
   * @param string $key The variable name
   * @return bool $exists The variable exists
   */
  function hasVariable(string $key): bool;
  
  /**
   * Retrieves a provided variable
   *
   * @param string $key The variable name
   * @return mixed
   */
  function getVariable(string $key): mixed;
  
  /**
   * Sets a provided variable
   *
   * @param string $key The variable name
   * @param mixed $value The variable value
   * @return void
   */
  function setVariable(string $key,mixed $value): void;
  
  /**
   * Assigns a variable
   *
   * alias for setVariable
   * @param string $key The variable name
   * @param mixed $value The variable value
   * @return void
   */
  function assign(string $key,mixed $value): void;
  
  /**
   * Assigns multiple variables
   *
   * @param array $value Associative array with variables
   * @return void
   */
  function assignMultiple(array $values): void;
  
  /**
   * The render function
   *
   * calls renderStatic internally
   * @return mixed
   */
  function render(): mixed;
  
  /**
   * The static render function
   *
   * @param string $fileName The template fileName
   * @param array $variables The template variables
   * @param string $renderer The renderer FQCN
   * @param string $encoding The encoding function for the rendered template
   * @return mixed
   */
  static function renderStatic(
    string $fileName,
    array $variables,
    string $renderer,
    string $encoding
  ): mixed;
}

?>