<?php

namespace Devworx;

use \Devworx\Utility\GeneralUtility;
use \Devworx\Utility\ModelUtility;
use \Devworx\Utility\DebugUtility;

abstract class AbstractModel {
   
  protected 
    $uid = 0,
    $cruser = 0,
    $hidden = false,
    $created = null,
    $updated = null,
    $deleted = null;
  
  public static function empty(...$args){
    return GeneralUtility::makeInstance( get_called_class(), ...$args );
  }
  
  public static function emptyArray(...$args){
    return ModelUtility::toArray( self::empty(...$args) );
  }
  
  public static function presetArray(array $preset){
    return array_merge( ModelUtility::toArray( self::empty() ), $preset );
  }
  
  public function fields():array {
    $result = [];
    foreach( get_object_vars($this) as $key => $value ){
      if( substr($key,0,1) === '*' )
        $key = substr($key,1,strlen($key)-2);
      $result[$key] = $value;
    }
    return $result;
  }
  
  public function toArray(): array {
    return ModelUtility::toArray($this);
  }
  
  public function getUid(): int {
    return $this->uid;
  }
  
  public function setUid(int $value): void {
    $this->uid = $value;
  }
  
  public function getCruser(): int {
    return $this->cruser;
  }
  
  public function setCruser(int $value): void {
    $this->cruser = $value;
  }
  
  public function getCreated(): ?\DateTime {
    return $this->created;
  }
  
  public function setCreated(?string $value): void {
    if( is_null($this->created) )
      $this->created = new \DateTime();
    $this->created->setTimestamp(intval($value));
  }
  
  public function getUpdated(): ?\DateTime {
    return $this->updated;
  }
  
  public function setUpdated(?string $value): void {
    $this->updated = new \DateTime($value);
  }
  
  public function getDeleted(): ?\DateTime {
    return $this->deleted;
  }
  
  public function setDeleted(?string $value): void {
    $this->deleted = new \DateTime($value);
  }
  
  public function getHidden(): bool {
    return $this->hidden;
  }
  
  public function setHidden(bool $value): void {
    $this->hidden = $value;
  }
  
}

?>