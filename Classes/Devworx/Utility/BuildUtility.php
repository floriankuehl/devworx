<?php

namespace Devworx\Utility;

class BuildUtility {
  
  const NULLABLE = ['TIMESTAMP','DATE','DATETIME'];
  
  public static function Comment(string $indent,...$lines){
    $result = array_map(function($line) use ($indent){
      return "{$indent} * {$line}";
    },$lines);
    $result = implode("\n",$result);
    return "/**\n{$result}\n{$indent} */";
  }
  
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
