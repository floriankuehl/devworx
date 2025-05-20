<?php

namespace Devworx\Interfaces;

/**
 * The interface for models
 */
interface IModel {
  
  	/**
	 * Returns an empty representation of a model
	 *
	 * @param array $args
 	 * @return object
	 */
	static function empty(...$args): object;
	
	/**
	 * Returns an empty array representation of a model
	 *
	 * @param array $args
 	 * @return array
	 */
	static function emptyArray(...$args): array;
	
	/**
	 * Returns an preset array representation of a model
	 *
	 * @param array $preset
 	 * @return array
	 */
	static function presetArray(array $preset): array;
	
	/**
	 * Returns all fields of a model
	 *
 	 * @return array
	 */
	function fields(): array;

	/**
	 * Returns an array representation of the current model
	 *
 	 * @return array
	 */
	function toArray(): array;
	
	/**
	 * Getter for the uid
	 *
 	 * @return int $value
	 */
	function getUid(): int;
	
	/**
	 * Setter for the uid
	 *
 	 * @param int $value
	 * @return void
	 */
	function setUid(int $value): void;
	
	/**
	 * Getter for the cruser
	 *
 	 * @return int $value
	 */
	function getCruser(): int;
	
	/**
	 * Setter for the cruser
	 *
	 * @param int $value
 	 * @return void
	 */
	function setCruser(int $value): void;
	
	/**
	 * Getter for the creation date
	 *
 	 * @return ?\DateTime $value
	 */
	function getCreated(): ?\DateTime;
	
	/**
	 * Setter for the creation date
	 *
	 * @param ?string $value The date in string form
 	 * @return void
	 */
	function setCreated(?string $value): void;
	
	/**
	 * Getter for the update date
	 *
 	 * @return ?\DateTime $value
	 */
	function getUpdated(): ?\DateTime;
	
	/**
	 * Setter for the update date
	 *
	 * @param ?string $value The date in string form
 	 * @return void
	 */
	function setUpdated(?string $value): void;
	
	/**
	 * Getter for the deletion date
	 *
 	 * @return ?\DateTime $value
	 */
	function getDeleted(): ?\DateTime;
	
	/**
	 * Setter for the deletion date
	 *
	 * @param ?string $value The date in string form
 	 * @return void
	 */
	function setDeleted(?string $value): void;
	
	/**
	 * Getter for the hidden flag
	 *
 	 * @return bool $value
	 */
	function getHidden(): bool;
	
	/**
	 * Setter for the hidden flag
	 *
 	 * @param bool $value
	 * @return void
	 */
	function setHidden(bool $value): void;
}

?>