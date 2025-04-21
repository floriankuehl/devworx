<?php

namespace Devworx\Interfaces;

/**
 * The interface for models
 */
interface IModel {
  
  static function empty(...$args): object;
  static function emptyArray(...$args): array;
  static function presetArray(array $preset): array;
  function fields(): array;
  
  function toArray(): array;
  function getUid(): int;
  function setUid(int $value): void;
  function getCruser(): int;
  function setCruser(int $value): void;
  function getCreated(): ?\DateTime;
  function setCreated(?string $value): void;
  function getUpdated(): ?\DateTime;
  function setUpdated(?string $value): void;
  function getDeleted(): ?\DateTime;
  function setDeleted(?string $value): void;
  function getHidden(): bool;
  function setHidden(bool $value): void;
}

?>