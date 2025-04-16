<?php

namespace Devworx;

use \Devworx\Utility\ModelUtility;
use \Devworx\Frontend;

class Repository {
  
  /** The default primary key */
  const DEFAULT_PK = 'uid';
  
  /** The default cache folder */
  const CACHEDIR = 'Repository';
  
  /** A list of system fields for models */
  const SYSTEM_FIELDS = [
    'uid',
    'cruser',
    'hidden',
    'created',
    'updated',
    'deleted'
  ];
  
  /** A list of system conditions for active rows */
  const SYSTEM_CONDITIONS = [
    'hidden=0',
    'ISNULL(deleted)',
  ];
  
  /** An index of mysql types for generating type placeholders for prepared statements */
  const TYPE_MAP = [
    'varchar' => 's',
    'text' => 's',
    'longtext' => 's',
    'int' => 'i',
    'tinyint' => 'i',
    'mediumint' => 'i',
    'bigint' => 'i',
    'float' => 'd',
    //'double' => 'd',
    'date' => 's',
    'datetime' => 's',
    'timestamp' => 's',
    'enum' => 's'
  ];
  
  /** A reference to the Database */
  protected $db = null;
  /** The current table */
  protected $table = '';
  /** The primary key of the table */
  protected $pk = '';
  /** The field details for the table */
  protected $details = [];
  /** The field list for the table */
  protected $fieldList = [];
  /** The type list of the table */
  protected $typeList = [];
  /** The type placeholders of the table */
  protected $valueList = [];
  /** The class to map after fetching */
  protected $mapToClass = '';
  
  public $defaultConditions = [];
  
  /** Checks if $field is a system field */
  public static function isSystemField(string $field){
    return in_array($field,self::SYSTEM_FIELDS);
  }
  /** Returns the system fields as a string if $string is true, otherwise returns an array */
  public static function getSystemFields(bool $string=false){
    return $string ? implode( ',', self::SYSTEM_FIELDS ) : self::SYSTEM_FIELDS;
  }
  
  /** Returns the system conditions as a string if $string is true, otherwise returns an array */
  public static function getSystemConditions(bool $string=false){
    return $string ? implode(" AND ",self::SYSTEM_CONDITIONS) : self::SYSTEM_CONDITIONS;
  }
  
  public function __construct($values,string $className=null){
    global $DB;
    
    $this->db = $DB;
    $this->pk = self::DEFAULT_PK;
    $this->defaultConditions = self::SYSTEM_CONDITIONS;
    
    if(!empty($values)){
      if( is_string($values) ){
        $this->table = $values;
      } elseif( is_array($values) ){
        foreach( $values as $k => $v ){
          if( property_exists($this,$k) )
            $this->$k = $v;
        }
      }
      
      if( !$this->loadCachedSettings() ){
        if( is_null($className) ){
          $className = "Frontend\\Models\\".ucfirst($this->table);
          if( class_exists($className) )
            $this->mapToClass = $className;
        } else
          $this->mapToClass = $className;
        
        if( empty($this->details) ){
          $this->initialize();
          $this->cacheSettings();
        }
      }
    }
  }
  
  /** Returns the url for the settings cache file */
  public function getCacheUrl(): string {
    return Frontend::path(
      Frontend::getConfig('system','cache'),
      self::CACHEDIR,
      ( ucfirst($this->table) . '.' . Frontend::$context . '.php' )
    );
  }
  
  /** Loads cached settings */
  public function loadCachedSettings(): bool {
    $fileName = $this->getCacheUrl();
    if( is_file($fileName) ){
      $data = json_decode( file_get_contents($fileName), true );
      $this->fromArray( $data );
      return true;
    }
    return false;
  }
  
  /** Caches the current settings */
  public function cacheSettings(): bool {
    $data = json_encode($this->toArray(),JSON_PRETTY_PRINT);
    $path = $this->getCacheUrl();
    return file_put_contents($path, $data) !== false;
  }
  
  /** Returns the last error message of $db */
  public function error(): string {
    return $this->db->error();
  }
  
  /** Checks if the set table contains a primary key */
  public function hasPK(): bool {
    $result = $this->db->query("
      SELECT EXISTS(
        SELECT 1
        FROM information_schema.columns
        WHERE 
           table_name='{$this->table}'
           AND column_name = '{$this->pk}'
           AND column_key = 'PRI'
      ) AS hasPK;
    ",true,MYSQLI_ASSOC);
    return intval($result['hasPK']) > 0;
  }
  
  /** Returns the primary key of the set table */
  public function getPK(): string {
    $result = $this->db->query("
      SELECT column_name AS pk 
      FROM information_schema.columns
      WHERE 
         table_name='{$this->table}'
         AND column_key = 'PRI';
    ",true,MYSQLI_ASSOC);
    return $result['pk'];
  }
  
  /** Initializes the repository and reads the type definitions of the set table */
  public function initialize(){
        
    $fieldList = [];
    $typeList = [];
    $valueList = [];
    $details = [];    
    
    $this->pk = $this->getPK();
    $explain = $this->db->query('EXPLAIN '.$this->table.';',false,MYSQLI_ASSOC);
    //echo \Devworx\Utility\DebugUtility::var_dump(['table'=>$this->table,'explain'=>$explain]);
    
    foreach( $explain as $i => $field ){
      $type = $field['Type'];
      $name = $field['Field'];
      //if( in_array($name,self::SYSTEM_FIELDS) ) continue;
      
      $pattern = '~([a-zA-Z]{1,})\((\d{1,})\)~';
      $length = 0;
      $found = preg_match($pattern,$type,$matches);
      //echo \Devworx\Utility\DebugUtility::var_dump(['source'=>$type,'pattern'=>$pattern,'matches'=>$matches]);
      if( !empty($found) ){
        $type = $matches[1];
        $length = intval($matches[2]);
        unset($matches);
      }
      
      if( array_key_exists($type,self::TYPE_MAP) ){
        $strType = self::TYPE_MAP[$type];
        $details[$name] = [
          $type,
          $length,
          $strType
        ];
        $fieldList []= $name;
        $typeList []= $strType;
        $valueList []= '?';
      }
    }
    
    $this->details = $details;
    $this->fieldList = $fieldList;
    $this->typeList = implode('',$typeList);
    $this->valueList = implode(',',$valueList);
  }
  
  /** Returns the current configuration as an array */
  public function toArray(): array {
    return [
      'table' => $this->table,
      'pk' => $this->pk,
      'fieldList' => $this->fieldList,
      'typeList' => $this->typeList,
      'valueList' => $this->valueList,
      'details' => $this->details,
      'defaultConditions' => $this->defaultConditions,
      'mapToClass' => $this->mapToClass,
    ];
  }
  
  /** Loads the current configuration from an array */
  public function fromArray(array $value): Repository {
    foreach( $value as $k => $v ){
      if( property_exists($this,$k) )
        $this->$k = $v;
    }
    return $this;
  }
  
  //------------------ PROPERTY FUNCTIONS ----------------------------
  
  /** Getter for the set primary key */
  public function getPrimaryKey(){
    return $this->pk;
  }
  
  /** Returns field details if $field is set, otherwise returns an array of all field details  */
  public function getDetails(string $field=''){
    return empty($field) ? $this->details : $this->details[$field];
  }
  
  /** Returns the field list as an array */
  public function getFieldList(){
    return $this->fieldList;
  }
  
  /** Returns the type list as an array */
  public function getTypeList(){
    return $this->typeList;
  }
  
  /** Returns the value placeholder list as an array */
  public function getValueList(){
    return $this->valueList;
  }
  
  /** Getter for class to map the row data to */
  public function getMapToClass(): string {
    return $this->mapToClass;
  }
  
  /** Setter for class to map the row data to */
  public function setMapToClass(string $className): void {
    $this->mapToClass = $className;
  }
  
  //------------------ QUERY FUNCTIONS ----------------------------
  
  /** Finds all rows of the set $table */
  public function findAll(string $fields='*',string $order='',int $offset=0,int $limit=0){
    $limit = $limit > 0 ? " LIMIT {$offset},{$limit}" : "";
    $order = " ORDER BY " . ( empty($order) ? "{$this->pk} ASC" : $order );
    $system = implode(" AND ",$this->defaultConditions);
    $result = $this->db->query("SELECT {$fields} FROM {$this->table} WHERE {$system}{$order}{$limit};",false,MYSQLI_ASSOC); 
    return empty($this->mapToClass) ? $result : ModelUtility::toModels( $result, $this->mapToClass );
  }
  
  /** Finds a subset of rows of the set $table based on $key and $value */
  public function findBy($key,$value,string $fields='*',string $order='',int $offset=0,int $limit=0){
    $limit = $limit > 0 ? " LIMIT {$offset},{$limit}" : "";
    $order = " ORDER BY " . (empty($order) ? "{$this->pk} ASC" : $order );
    $system = implode(" AND ",$this->defaultConditions);
    $result = $this->db->query("SELECT {$fields} FROM {$this->table} WHERE ({$key} = '{$value}') AND {$system}{$order}{$limit};",false,MYSQLI_ASSOC);
    return empty($this->mapToClass) ? $result : ModelUtility::toModels( $result, $this->mapToClass );
  }
  
  /** Finds a single row of the set $table based on $key and $value */
  public function findOneBy($key,$value,string $fields='*'){
    $system = implode(" AND ",$this->defaultConditions);
    $result = $this->db->query("SELECT {$fields} FROM {$this->table} WHERE ({$key} = '{$value}') AND {$system} LIMIT 1;",true,MYSQLI_ASSOC);
    return empty($this->mapToClass) ? $result : ModelUtility::toModel( $result, $this->mapToClass );
  }
  
  /** Finds a subset of rows of the set $table by a given associative $filter array */
  public function filter(
    array $filter,
    string $fields='*',
    string $order='',
    int $offset=0,
    int $limit=0
  ){
    
    if( empty($filter) ) return [];
    $conditions = [...$this->defaultConditions];
    
    $limit = empty($limit) ? "" : " LIMIT " . ( empty($offset) ? $limit : "{$offset}, {$limit}" );
    $order = empty($order) ? $order : " ORDER BY {$order}";
    
    foreach( $filter as $k=>$v ){
      if( array_key_exists($k,$this->details) ){
        $details = $this->details[$k];
        switch($details[0]){
          case'varchar':{ 
            if( !empty($v) ){
              if( is_array($v) ){
                $v = implode(',',array_reduce($v,function($acc,$it){
                  $acc []= "'{$v}'";
                },[]));
                $conditions []= "{$k} IN ({$v})";
              } else {
                $v = str_replace('*','%',$v);
                $conditions[]= "{$k} LIKE '{$v}'";
              }
            }
          }break;
          case'text':{
            if( !empty($v) ){
              $v = str_replace('*','%',$v);
              $conditions[]= "{$k} LIKE '{$v}'";
            }
          }break;
          case'tinyint':{
            $v = intval($v);
            if( $v >= 0 ) $conditions[]= "{$k} = {$v}";
          }break;
          case'int':{
            if( is_array($v) ){
              if( !empty($v) ){
                $v = implode(',',$v);
                $conditions []= "{$k} IN ({$v})";
              }
            } else {
              $v = intval($v);
              if( $v > 0 ) $conditions[]= "{$k} = {$v}";
            }
          }break;
          case'bigint':{
            if( is_array($v) ){
              if( !empty($v) ){
                $v = implode(',',$v);
                $conditions []= "{$k} IN ({$v})";
              }
            } else {
              $v = intval($v);
              if( $v > 0 ) $conditions[]= "{$k} = {$v}";
            }
          }break;
          case'float':{
            $v = floatval($v);
            if( $v > 0 ) $conditions[]= "{$k} = {$v}";
          }break;
          case'datetime':
          case'timestamp': {
            if( !empty($v) ){
              if( is_array($v) ){
                $from = $v[0];
                $to = $v[1];
                $conditions[]= "{$k} BETWEEN '{$from}' AND '{$to}'";
              } else {
                $v = explode(' ',strval($v));
                switch( count($v) ){
                  case 1: {
                    $v = $v[0];
                    $conditions[]= "{$k} BETWEEN '{$v} 00:00:00' AND '{$v} 23:59:59'";
                  } break;
                  default:{
                    $conditions[]= "{$k} = '{$v}'"; 
                  } break;
                }
              }
            }
          }break;
        }
      }
    }
        
    $conditions = implode(" AND ",$conditions);
    
    $result = $this->db->query("SELECT {$fields} FROM {$this->table} WHERE {$conditions}{$order}{$limit};",false,MYSQLI_ASSOC);
    return empty($this->mapToClass) ? $result : ModelUtility::toModels( $result, $this->mapToClass );
  }
  
  /** Counts all rows of the set $table */
  public function count(): int {
    $system = implode(" AND ",$this->defaultConditions);
    $result = $this->db->query("SELECT COUNT({$this->pk}) FROM {$this->table} WHERE {$system} LIMIT 1;",true);
    return $result[0];
  }
  
  /** Counts rows of the set $table by $field and $value */
  public function countBy(string $field,$value): int {
    if( in_array($field,$this->fieldList) ){
      $system = implode(" AND ",$this->defaultConditions);
      $result = $this->db->query("SELECT COUNT({$this->pk}) FROM {$this->table} WHERE {$field}='{$value}' AND {$system} LIMIT 1;",true);
      return $result[0];
    }
    return -1;
  }
  
  /** Finds a row by the $pk of the set $table with value $uid */
  public function findByUid($uid,string $fields='*'){
    $system = implode(" AND ",$this->defaultConditions);
    $result = $this->db->query("SELECT {$fields} FROM {$this->table} WHERE ({$this->pk} = {$uid}) AND {$system} LIMIT 1;",true,MYSQLI_ASSOC);
    return empty($this->mapToClass) ? $result : ModelUtility::toModel( $result, $this->mapToClass );
  }
  
  /** Adds a row to the set $table */
  public function add(array $data): int {
    return $this->db->add($this->table,$data);
  }
  
  /** Adds many rows to the set $table */
  public function addAll(array $rows): array {
    $result = [];
    foreach( $rows as $i =>$row ){
      $result []= $this->add($row);
    }
    return $result;
  }
  
  /** Updates a given row with $data */
  public function put(array $data): bool {   
    $uid = null;
    $fields = [];
    $types = [];
    $prepared = [];
    
    if( array_key_exists('uid',$data) ){
      $uid = (int)$data['uid'];
      foreach( $data as $field => $value ){
        $prepared []= $value;
        $fields []= "{$field}=?";
        $types []= $this->details[$field][2];
      }
    } else {
      if( count($data) == count($this->fieldList) ){
        $prepared = $data;
        $uid = $data[0];
        foreach( $this->fieldList as $i => $field ){
          $fields []= "{$field}=?";
          $types []= $this->details[$field][2];
        }
      } else {
        throw new \Exception("Data length must match fieldList length");
        return false;
      }
    }
    
    $fields = implode(',',$fields);
    $types = implode('',$types);
    $prepared []= $uid;
    return $this->db->prepare(
      "UPDATE {$this->table} SET {$fields} WHERE {$this->pk} = ? AND ISNULL(deleted);",
      "{$types}i",
      $prepared
    );
  }
  
  /** Sets the deleted timestamp of a row by its $pk $uid */
  public function remove($uid){
    if( is_array($uid) ){
      if( empty($uid) ) return null;
      $uid = implode(',',$uid);
      return $this->db->query("UPDATE {$this->table} SET deleted=CURRENT_TIMESTAMP WHERE {$this->pk} IN ({$uid});",true,MYSQLI_NUM);
    }
    return $this->db->query("UPDATE {$this->table} SET deleted=CURRENT_TIMESTAMP WHERE {$this->pk} = '{$uid}';",true,MYSQLI_NUM);
  }
  
  /** Unsets the deleted timestamp of a row by its $pk $uid */
  public function recycle($uid){
    if( is_array($uid) ){
      if( empty($uid) ) return null;
      $uid = implode(',',$uid);
      return $this->db->query("UPDATE {$this->table} SET deleted=NULL WHERE {$this->pk} IN ({$uid});",true,MYSQLI_NUM);
    }
    return $this->db->query("UPDATE {$this->table} SET deleted=NULL WHERE {$this->pk} = '{$uid}';",true,MYSQLI_NUM);
  }
  
  /** Deletes a row completely by its $pk $uid */
  public function delete($uid){
    if( is_array($uid) ){
      if( empty($uid) ) return null;
      $uid = implode(',',$uid);
      return $this->db->query("DELETE FROM {$this->table} WHERE {$this->pk} IN ({$uid});",true,MYSQLI_NUM);
    }
    return $this->db->query("DELETE FROM {$this->table} WHERE {$this->pk} = '{$uid}';",true,MYSQLI_NUM);
  }
}


?>
