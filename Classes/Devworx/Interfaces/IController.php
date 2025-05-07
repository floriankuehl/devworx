<?php

namespace Devworx\Interfaces;

/**
 * Interface for basic controllers
 */
interface IController {
  function getId(): string;
  function getNamespace(): string;
  function getBlockRendering(): bool;
  function setBlockRendering(bool $value=true): void;
  function getBlockLayout(): bool;
  function setBlockLayout(bool $value=true): void;
  function getRequest(): IRequest;
  function getView(): IView;
  
  function initialize();
  function processAction(string $action,...$arguments);
}

?>