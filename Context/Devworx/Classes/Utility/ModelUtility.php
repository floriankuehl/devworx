<?php

namespace Devworx\Utility;

use \Devworx\Models\AbstractModel;

class ModelUtility {
  
  /**
   * Sets data in a model by setter
   * 
   * @param AbstractModel $model
   * @param array $row
   * @return AbstractModel
   */
  public static function hydrateModel(AbstractModel $model, array $row): AbstractModel {
	foreach( $row as $key => $value ){
	  $model->{"set".ucfirst($key)}($value);
	}
	return $model;
  }
  
  /**
   * Maps a data row to a new model instance by setter functions
   *
   * @param array $row The data array
   * @param string $class The FQCN of the new instance
   * @return AbstractModel
   */
  public static function toModel(array $row, string $class): AbstractModel {
    if( empty($class) )
      throw new \Exception("No class provided");
    if( !is_a($class,AbstractModel::class,true) )
      throw new \Exception("Class must inherit from AbstractModel");
    
	return self::hydrateModel(new $class(),$row);
  }
  
  /**
   * Maps data rows to new model instances by setter functions
   *
   * @param array $rows The list of data arrays
   * @param string $class The FQCN of the new instance
   * @return array
   */
  public static function toModels(array $rows,string $class): array {
    if( empty($class) )
      throw new \Exception("No class provided");
    if( !is_a($class,AbstractModel::class,true) )
      throw new \Exception("Class must inherit from AbstractModel");
    
    $result = [];
    foreach( $rows as $i => $row ){
	  $result []= self::hydrateModel(new $class(),$row);
    }
    return $result;
  }
  
  /**
   * Converts a model to an array
   *
   * @param AbstractModel $model The model to convert to an array
   * @return array
   */
  public static function toArray(AbstractModel $model):array {
    $result = [];
    
    foreach( $model->fields() as $key => $value ){
      $value = $model->{"get".ucfirst($key)}();
      
      if( is_null( $value ) ){
        //$result[$key] = $value;
        continue;
      }
      
      if( is_object($value) ){
        if( $value instanceof \DateTime ){
          $result[$key] = $value->getTimestamp();
          continue;
        }
        if( is_a( $value, IModel::class ) ){
          $result[$key] = $value->getUid();
          continue;
        }
      }
      if( is_bool($value) )
        $value = $value ? 1 : 0;
      $result[$key] = $value;
    }
    return $result;
  }
  
}

?>
