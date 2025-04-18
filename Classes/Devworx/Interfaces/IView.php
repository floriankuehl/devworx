<?php

namespace Devworx;

/**
 * The interface for general Views
 */
interface IView {
  
  function getId(): string;
  function setId(string $id): void;
  function getFile(): string;
  function setFile(string $fileName): void;
  function getProvideAll(): bool;
  function setProvideAll(bool $all): void;
  function getEncoding(): string;
  function setEncoding(string $encoding): void;
  function getVariables(): array;
  function setVariables(array $variables): void;
  function getRenderer(): string;
  function setRenderer(string $renderer): void;
  
  function hasVariable(string $key): bool;
  function getVariable(string $key);
  function setVariable(string $key,$value): void;
  
  function assign(string $key,$value): void;
  function assignMultiple(array $values): void;
  
  function render();
  static function renderStatic(
    string $fileName,
    array $variables,
    string $renderer,
    string $encoding
  );
}

?>