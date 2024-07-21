<?php

namespace Devworx\Utility;

class BuildUtility {
  
  public static function Comment(string $indent,...$lines){
    $result = array_map(function($line) use ($indent){
      return "{$indent} * {$line}";
    },$lines);
    $result = implode("\n",$result);
    return "/****\n{$result}\n{$indent} **/";
  }
  
  public static function Model(string $namespace,string $className,string $table,string $pk='uid'): string {
    
    $systemFields = [$pk,'cruser','created','updated','deleted','hidden'];
    
    $extends = \Devworx\AbstractModel::class;
    $repository = new \Devworx\Repository([
      'table' => $table,
      'pk' => $pk
    ]);
    $properties = [];
    $functions = [];
    
    $indent = "  ";
    
    //echo DebugUtility::var_dump($repository);
    
    foreach($repository->getDetails() as $name => $info ){
      
      if( in_array($name,$systemFields) )
        continue;
      
      $getter = "get" . ucfirst($name);
      $setter = "set" . ucfirst($name);
      
      $standard = "''";
      $inputType = 'string';
      $returnType = 'string';
      $returnNullable = '';
      $argument = '$value';
      $argumentNullable = '';
      $value = $argument;
      $property = '$this->'.$name;
            
      switch( $info[0] ){
        case'varchar':{}break;
        case'text':{}break;
        case'int':{ 
          $standard = '0';
          $inputType = 'int';
          $returnType = 'int'; 
        }break;
        case'mediumint':{ 
          $standard = '0';
          $inputType = 'int';
          $returnType = 'int';
        }break;
        case'bigint':{ 
          $standard = '0';
          $inputType = 'int';
          $returnType = 'int';
        }break;
        case'float':{ 
          $standard = '0.0';
          $inputType = 'float';
          $returnType = 'float'; 
        }break;
        case'tinyint':{ 
          $standard = 'false';
          $returnType = 'bool'; 
          $inputType = 'bool';
        }break;
        case'date':{
          $standard = 'null';
          $returnNullable = '?';
          $argumentNullable = '?';
          $returnType = '\DateTime';
          $inputType = 'string';
          $value = 'new \DateTime($value)';
        }break;
        case'datetime':{
          $standard = 'null';
          $returnNullable = '?';
          $argumentNullable = '?';
          $returnType = '\DateTime';
          $inputType = 'string';
          $value = 'new \DateTime($value)';
        }break;
        case'timestamp':{
          $standard = 'null';
          $returnNullable = '?';
          $argumentNullable = '?';
          $returnType = '\DateTime';
          $inputType = 'string';
          $value = 'new \DateTime($value)';
        }break;
      }
      
      $properties []= self::Comment($indent,
        "{$name} of the {$className}",
        "",
        "@param {$returnType} " . '$' . $name
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
    
    
    $properties = implode("\n{$indent}",$properties);
    $functions = implode("\n{$indent}",$functions);
    
    return implode(PHP_EOL,[
      '<?php',
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
  
  
  public static function checkModels(){
    
    global $DB;
    
    $tables = array_column( $DB->query("SHOW TABLES;"), 0 );
    $namespace = "Frontend";
    
    foreach($tables as $table){
      $className = ucfirst($table);
      $fileName = "./Classes/{$namespace}/Models/{$className}.php";
      if( is_file($fileName) ) continue;
      $code = self::Model($namespace . '\Models',$className,"{$table}");
      file_put_contents($fileName,$code);
      echo "{$namespace}\\Models\\{$className} in {$fileName}<br>";
    }
  }
}
?>