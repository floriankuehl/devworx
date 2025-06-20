<?php

namespace Devworx\Models;

use \Devworx\Interfaces\IModel;
use \Devworx\Utility\GeneralUtility;
use \Devworx\Utility\ModelUtility;

/**
 * The base class for models
 */

abstract class AbstractModel implements IModel {
  
	/** 
	 * @var int $uid The uid of the model 
	 */
	protected $uid = 0;
	/** 
	 * @var int $cruser The uid of the creator of the model 
	 */
	protected $cruser = 0;
	/** 
	 * @var bool $hidden Defines if the model is hidden
	 */
	protected $hidden = false;
	/** 
	 * @var \DateTime $created The creation date of the model 
	 */
	protected $created = null;
	/** 
	 * @var \DateTime $updated The last update of the model 
	 */
	protected $updated = null;
	/** 
	 * @var \DateTime $deleted The deletion date of the model 
	 */
	protected $deleted = null;
  
	/** 
	 * Creates an empty instance of the called model class 
	 *
	 * @param array $args The arguments passed to the constructor of the model
	 * @return object
	 */
	public static function empty(...$args): object {
		return GeneralUtility::makeInstance( get_called_class(), ...$args );
	}
  
	/** 
	 * Converts any model structure into an empty array with respective keys 
	 *
	 * @param array $args The arguments passed to the 'empty' function
	 * @return array
	 */
	public static function emptyArray(...$args): array {
		return ModelUtility::toArray( self::empty(...$args) );
	}
  
	/** 
	 * Converts any model structure into an empty array with respective keys including preset data 
	 * @param array $preset The preset data for the model array
	 * @return array
	 */
	public static function presetArray(array $preset): array {
		return array_merge( ModelUtility::toArray( self::empty() ), $preset );
	}

	/** 
	 * Converts the fields of a model into an array 
	 * 
	 * @return array
	 */
	public function fields():array {
		$result = [];
		foreach( get_object_vars($this) as $key => $value ){
			if( substr($key,0,1) === '*' )
				$key = substr($key,1,strlen($key)-2);
			$result[$key] = $value;
		}
		return $result;
	}

	/** 
	 * Converts this model into an array using ModelUtility 
	 * 
	 * @return array
	 */
	public function toArray(): array {
		return ModelUtility::toArray($this);
	}

	/** 
	 * getter for the uid 
	 *
	 * @return int
	 */
	public function getUid(): int {
		return $this->uid;
	}

	/** 
	 * setter for the uid 
	 *
	 * @param int $value
	 * @return void
	 */
	public function setUid(int $value): void {
		$this->uid = $value;
	}

	/** 
	 * getter for the creator
	 *
	 * @return int
	 */
	public function getCruser(): int {
		return $this->cruser;
	}

	/** 
	 * setter for the creator
	 *
	 * @param int $value
	 * @return void
	 */
	public function setCruser(int $value): void {
		$this->cruser = $value;
	}

	/** 
	 * getter for the creation date
	 *
	 * @return \DateTime
	 */
	public function getCreated(): ?\DateTime {
		return $this->created;
	}

	/** 
	 * setter for the creation date
	 *
	 * @param \DateTime $value
	 * @return void
	 */
	public function setCreated(?string $value): void {
		if( is_null($this->created) )
			$this->created = new \DateTime();
		$this->created->setTimestamp(intval($value));
	}

	/** 
	 * getter for the update date
	 *
	 * @return \DateTime
	 */
	public function getUpdated(): ?\DateTime {
		return $this->updated;
	}

	/** 
	 * setter for the update date
	 *
	 * @param \DateTime $value
	 * @return void
	 */
	public function setUpdated(?string $value): void {
		if( is_null($this->updated) )
			$this->updated = new \DateTime();
		$this->updated->setTimestamp(intval($value));
	}

	/** 
	 * getter for the deletion date
	 *
	 * @return \DateTime
	 */
	public function getDeleted(): ?\DateTime {
		return $this->deleted;
	}

	/** 
	 * setter for the deletion date
	 *
	 * @param \DateTime $value
	 * @return void
	 */
	public function setDeleted(?string $value): void {
		if( is_null($this->deleted) )
			$this->deleted = new \DateTime();
		$this->deleted->setTimestamp(intval($value));
	}

	/** 
	 * getter for the hidden flag
	 *
	 * @return bool
	 */
	public function getHidden(): bool {
		return $this->hidden;
	}

	/** 
	 * setter for the hidden flag
	 *
	 * @param bool $value
	 * @return void
	 */
	public function setHidden(bool $value): void {
		$this->hidden = $value;
	}
  
}

?>
