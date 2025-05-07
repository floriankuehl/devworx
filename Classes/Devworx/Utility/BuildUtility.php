<?php

namespace Devworx\Utility;

use \Devworx\Repository;

class BuildUtility {
  
  const NULLABLE = ['TIMESTAMP','DATE','DATETIME'];
  
  /**
   * Creates a multiline PHP comment
   *
   * @param string $indent The line indent to use
   * @param array $lines The text lines for the comment
   * @return string
   */
  public static function Comment(string $indent,...$lines): string {
    $result = array_map(function($line) use ($indent){
      return "{$indent} * {$line}";
    },$lines);
    $result = implode("\n",$result);
    return "/**\n{$result}\n{$indent} */";
  }
  
  /**
   * Checks a model against a SQL table and retrieves the needed changes
   *
   * @param string $className The FQCN of the model
   * @return array
   */
  public static function ClassToTable(string $className): array {
    
    //$model = GeneralUtility::makeInstance($className);
    
    $class = new \ReflectionClass($className);
    $tableName = strtolower( $class->getShortName() );
    $repository = new \Devworx\Repository($tableName);
    $knownFields = $repository->getDetails();
    
    $fields = [];
    $later = [];
    $create = [];
    $lastKnown = '';
    
    $properties = $class->getProperties( \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED );
    
    foreach( $class->getProperties() as $i => $property ){
      $name = $property->getName();
      $info = ArrayUtility::key($knownFields,$name,null);
      $isPK = $name == $repository->getPrimaryKey();
      if( is_array( $info ) ){
        unset($knownFields[$name]);
        $lastKnown = $name;
      }
      
      if( $isPK ){
        $type = strtoupper( $info[0] );
        $length = $info[1] > 0 ? '('.$info[1].')' : '';
        array_unshift($fields, "{$name} {$type}{$length} NOT NULL AUTO_INCREMENT");
        continue;
      }
  
      if( is_array($info) ){
        $type = strtoupper( $info[0] );
        $length = $info[1] > 0 ? '('.$info[1].')' : '';
        $null = ( in_array($type,self::NULLABLE) ? '' : 'NOT ') . 'NULL';
        
        if( $repository->isSystemField($name) )
          $later[]= "{$name} {$type}{$length} {$null}";
        else
          $fields[]= "{$name} {$type}{$length} {$null}";
        continue;
      }
      
      $getter = $class->getMethod( 'get' . ucfirst($name) );
      if( $getter ){
        $type = $getter->getReturnType()->getName();
        switch($type){
          case'int':{
            $create[] = "{$name} INT(11) NOT NULL";
          }break;
          case'float':{
            $create[] = "{$name} FLOAT NOT NULL";
          }break;
          case'bool':{
            $create[] = "{$name} TINYINT(1) NOT NULL";
          }break;
          case'string':{
            $create[] = "{$name} VARCHAR(64) NOT NULL";
          }break;
          case'\DateTime':{
            $create[] = "{$name} TIMESTAMP NULL";
          }break;
          case'array':{
            $create[] = "{$name} TEXT NOT NULL";
          }break;
          case'\Array':{
            $create[] = "{$name} TEXT NOT NULL";
          }break;
        }
      }
    }
    
    unset($class);
    
    $alterModel = [];
    
    if( !empty($knownFields) ){
      $functions = [];
      $properties = [];
      foreach( $knownFields as $name => $info ){
        $field = self::TableToClass( $info[0], $info[1] );
        self::CreateProperty( $className, $name, $field, $properties, $functions );
      }
      $alterModel = [
        'properties' => $properties,
        'functions' => $functions,
      ];
    }
    
    $fields = array_map(
      fn($field)=>"  {$field}",
      [
        ...$fields,
        ...$later
      ]
    );
    
    $createTable = implode(PHP_EOL,[
      "CREATE TABLE IF NOT EXISTS {$tableName} (",
      implode(','.PHP_EOL,$fields),
      ") ENGINE=InnoDB;",
    ]);
    
    $alters = [];
    if( !$repository->hasPK() ){
      $alters []= "ADD PRIMARY KEY (" . $repository->getPrimaryKey() . ")";
    }
    
    $alters = [
      ...$alters,
      ...array_map( fn($field)=>"ADD COLUMN {$field}", $create )
    ];
    $alterTable = empty($alters) ? "" : ( "ALTER TABLE {$tableName} " . implode(','.PHP_EOL.'  ',$alters) . ";" );
    
    unset($repository);
    
    return [
      'create' => $createTable,
      'modify' => $alterTable,
      'model' => $alterModel,
      'modelNeedsUpdate' => !empty($alterModel),
      'dbNeedsUpdate' => !empty($alterTable),
    ];
  }
  
  /**
   * Converts a SQL data type to an array of informations for the getter and setter of a model
   *
   * @param string $type The SQL type
   * @param int $length The SQL field length
   * @return array
   */
  public static function TableToClass(string $type,int $length): array {
    $result = [
      'standard' => "''",
      'inputType' => 'string',
      'returnType' => 'string',
      'returnNullable' => '',
      'argumentNullable' => '',
      'value' => '$value',
    ];
          
    switch( $type ){
      case'varchar':{}break;
      case'text':{}break;
      case'int':{ 
        $result['standard'] = '0';
        $result['inputType'] = 'int';
        $result['returnType'] = 'int'; 
      }break;
      case'mediumint':{ 
        $result['standard'] = '0';
        $result['inputType'] = 'int';
        $result['returnType'] = 'int';
      }break;
      case'bigint':{ 
        $result['standard'] = '0';
        $result['inputType'] = 'int';
        $result['returnType'] = 'int';
      }break;
      case'float':{ 
        $result['standard'] = '0.0';
        $result['inputType'] = 'float';
        $result['returnType'] = 'float'; 
      }break;
      case'tinyint':{ 
        if( $length == 1 ){
          $result['standard'] = 'false';
          $result['returnType'] = 'bool'; 
          $result['inputType'] = 'bool';
        } else {
          $result['standard'] = '0';
          $result['inputType'] = 'int';
          $result['returnType'] = 'int';
        }
      }break;
      case'date':{
        $result['standard'] = 'null';
        $result['returnNullable'] = '?';
        $result['argumentNullable'] = '?';
        $result['returnType'] = '\DateTime';
        $result['inputType'] = 'string';
        $result['value'] = 'new \DateTime($value)';
      }break;
      case'datetime':{
        $result['standard'] = 'null';
        $result['returnNullable'] = '?';
        $result['argumentNullable'] = '?';
        $result['returnType'] = '\DateTime';
        $result['inputType'] = 'string';
        $result['value'] = 'new \DateTime($value)';
      }break;
      case'timestamp':{
        $result['standard'] = 'null';
        $result['returnNullable'] = '?';
        $result['argumentNullable'] = '?';
        $result['returnType'] = '\DateTime';
        $result['inputType'] = 'string';
        $result['value'] = 'new \DateTime($value)';
      }break;
    }
    return $result;
  }
  
  /**
   * Creates a PHP model definition for a given SQL table
   *
   * @param string $namespace The namespace of the new model
   * @param string $className The class name of the new model
   * @param string $table The SQL table for the underlying data
   * @param string $pk The primary key of the SQL table
   * @param string $indent The line indent for the model
   * @return string
   */
  public static function Model(string $namespace,string $className,string $table,string $pk='uid',string $indent='  '): string {
    
    $extends = \Devworx\AbstractModel::class;
    $repository = new \Devworx\Repository([
      'table' => $table,
      'pk' => $pk
    ]);
    $properties = [];
    $functions = [];
    
    foreach($repository->getDetails() as $name => $info ){
      
      if( in_array($name,Repository::SYSTEM_FIELDS) )
        continue;
      
      $field = self::TableToClass( $info[0], $info[1] );
      self::CreateProperty( $className, $name, $field, $properties, $functions, $indent );
    }
    
    $properties = implode("\n{$indent}",$properties);
    $functions = implode("\n{$indent}",$functions);
    
    return implode(PHP_EOL,[
      '<?php',
      '',
      "namespace {$namespace};",
      '',
      self::Comment("","{$className} Model for table {$table}"),
      '',
      "class {$className} extends \\{$extends}",
      '{',
        "{$indent}{$properties}",
        '',
        "{$indent}{$functions}",
      '}',
      '',
      '?>'
    ]);
    
  }
  
  /**
   * Creates getter and setter for given model
   *
   * @param string $className The class name of the model
   * @param string $name The name of the model property
   * @param array $info The property type info
   * @param array $properties The resulting properties for the model
   * @param array $functions The resulting getters and setters for the model
   * @param string $indent The line indent for the functions
   * @return void
   */
  public static function CreateProperty( 
    string $className,
    string $name,
    array $info,
    array &$properties,
    array &$functions,
    string $indent = '  ',
  ): void {
    $tokens = explode('\\',$className);
    $className = array_pop( $tokens );
    
    $getter = "get" . ucfirst($name);
    $setter = "set" . ucfirst($name);
    $property = '$this->'.$name;
    
    $argument = '$value';
    $argumentNullable = $info['argumentNullable'];
    $inputType = $info['inputType'];
    $returnType = $info['returnType'];
    $returnNullable = $info['returnNullable'];
    $standard = $info['standard'];
    $value = $info['value'];
    
    $properties []= self::Comment($indent,
      "{$name} of the {$className}",
      "",
      "@var {$returnType} " . '$' . $name
    );
    $properties []= 'protected $'."{$name} = {$standard};\n";
    
    $functions []= self::Comment($indent,
      "Gets the {$className}s {$name}",
      "",
      "@return {$returnType}"
    );
    $functions []= "public function {$getter}(): {$returnNullable}{$returnType} {\n{$indent}{$indent}return {$property};\n{$indent}}\n";
    
    $functions []= self::Comment($indent,
      "Sets the {$className}s {$name}",
      "",
      "@param {$inputType} {$argument}",
      "@return void"
    );
    $functions []= "public function {$setter}({$argumentNullable}{$inputType} {$argument}): void {\n{$indent}{$indent}{$property} = {$value};\n{$indent}}\n";
  }
  
  /**
   * Checks if all tables in the database have models and creates them
   *
   * @return void
   */
  public static function checkModels(): void {
    
    global $DB;
    
    $tables = array_column( $DB->query("SHOW TABLES;"), 0 );
    $namespace = "Frontend";
    
    foreach($tables as $table){
      $className = ucfirst($table);
      $fileName = \Devworx\Frontend::path("Classes",$namespace,"Models","{$className}.php");
      if( is_file($fileName) ) continue;
      $code = self::Model($namespace . '\Models',$className,"{$table}");
      file_put_contents($fileName,$code);
      echo "{$namespace}\\Models\\{$className} in {$fileName}<br>";
    }
  }
}
?>
