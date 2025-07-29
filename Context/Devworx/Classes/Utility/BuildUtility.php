<?php

namespace Devworx\Utility;

use \Devworx\Database;
use \Devworx\Caches;
use \Devworx\Devworx;
use \Devworx\Repository\AbstractRepository;
use \Devworx\Models\AbstractModel;
use \ReflectionClass;
use \ReflectionProperty;
use \ReflectionMethod;

class BuildUtility {

	const NULLABLE = ['TIMESTAMP','DATE','DATETIME'];
	const DEFAULT_STRING_LENGTH = 64;

	private static array $typeMap = [
		'varchar' => [
		  'standard' => "''",
		  'inputType' => 'string',
		  'returnType' => 'string',
		  'returnNullable' => '',
		  'argumentNullable' => '',
		  'value' => '$value',
		],
		'text' => [
		  'standard' => "''",
		  'inputType' => 'string',
		  'returnType' => 'string',
		  'returnNullable' => '',
		  'argumentNullable' => '',
		  'value' => '$value',
		],
		'int' => [
		  'standard' => '0',
		  'inputType' => 'int',
		  'returnType' => 'int',
		  'returnNullable' => '',
		  'argumentNullable' => '',
		  'value' => '$value',
		],
		'float' => [
		  'standard' => '0.0',
		  'inputType' => 'float',
		  'returnType' => 'float',
		  'returnNullable' => '',
		  'argumentNullable' => '',
		  'value' => '$value',
		],
		'tinyint' => [
		  'standard' => 'false',
		  'inputType' => 'bool',
		  'returnType' => 'bool',
		  'returnNullable' => '',
		  'argumentNullable' => '',
		  'value' => '$value',
		],
		'date' => [
		  'standard' => 'null',
		  'inputType' => 'string',
		  'returnType' => '\\DateTime',
		  'returnNullable' => '?',
		  'argumentNullable' => '?',
		  'value' => 'new \\DateTime($value)',
		],
		'datetime' => [
		  'standard' => 'null',
		  'inputType' => 'string',
		  'returnType' => '\\DateTime',
		  'returnNullable' => '?',
		  'argumentNullable' => '?',
		  'value' => 'new \\DateTime($value)',
		],
		'timestamp' => [
		  'standard' => 'null',
		  'inputType' => 'string',
		  'returnType' => '\\DateTime',
		  'returnNullable' => '?',
		  'argumentNullable' => '?',
		  'value' => 'new \\DateTime($value)',
		],
	];

	public static function Comment(string $indent,...$lines): string {
		$result = array_map(fn($line) => "{$indent} * {$line}", $lines);
		return "/**\n" . implode("\n", $result) . "\n{$indent} */";
	}

	public static function TableToClass(string $type,int $length): array {
		$type = strtolower($type);
		if ($type === 'tinyint' && $length > 1) {
		  return self::$typeMap['int'];
		}
		return self::$typeMap[$type] ?? [
		  'standard' => "''",
		  'inputType' => 'string',
		  'returnType' => 'string',
		  'returnNullable' => '',
		  'argumentNullable' => '',
		  'value' => '$value',
		];
	}

	public static function CreateProperty( 
		string $className,
		string $name,
		array $info,
		array &$properties,
		array &$functions,
		string $indent = "\t"
	): void {
		if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
		  throw new \InvalidArgumentException("Invalid property name: {$name}");
		}
		$tokens = explode('\\',$className);
		$className = array_pop($tokens);

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
		  "@var {$returnType} \${$name}"
		);
		$properties []= "protected \${$name} = {$standard};\n";

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
	 * Creates a PHP model definition for a given SQL table
	 *
	 * @param string $namespace The namespace of the new model
	 * @param string $className The class name of the new model
	 * @param string $table The SQL table for the underlying data
	 * @param string $pk The primary key of the SQL table
	 * @param string $indent The line indent for the model
	 * @return string
	 */
	public static function Model(
		string $namespace, 
		string $className, 
		string $table, 
		string $pk = 'uid', 
		string $indent = "\t"
	): string
	{
		$config = Caches::get('Repository')->get($namespace,$table);
		$config['pk'] = $pk;
		
		$extends = AbstractModel::class;
		
		$properties = [];
		$functions = [];

		foreach($config['details'] as $name => $info) {
			if (in_array($name, Database::SYSTEM_FIELDS)) {
				continue;
			}
			$field = self::TableToClass($info[0], $info[1]);
			self::CreateProperty($className, $name, $field, $properties, $functions, $indent);
		}

		$propertiesBlock = implode("\n{$indent}", $properties);
		$functionsBlock = implode("\n{$indent}", $functions);

		return implode(PHP_EOL, [
			'<?php',
			'',
			"namespace {$namespace}\\Models;",
			'',
			self::Comment('', "{$className} Model for table {$table}"),
			'',
			"class {$className} extends \\{$extends}",
			'{',
			"{$indent}{$propertiesBlock}",
			'',
			"{$indent}{$functionsBlock}",
			'}',
			'',
			'?>',
		]);
	}
	
	/**
	 * Creates a PHP repository definition for a given SQL table
	 *
	 * @param string $namespace The namespace of the new model
	 * @param string $table The SQL table for the underlying data
	 * @param string $pk The primary key of the SQL table
	 * @param string $indent The line indent for the model
	 * @return string
	 */
	public static function Repository(
		string $namespace, 
		string $table,
		string $pk='uid',
		string $indent = "\t"
	){
		$modelClass = ucfirst($table);
		
		$functions = [
			'public function __construct(...$args){',
			"{$indent}" . 'parent::__construct([',
			"{$indent}{$indent}" . "'table' => '{$table}',",
			"{$indent}{$indent}" . "'pid' => '{$pk}',",
			"{$indent}{$indent}" . "'mapToClass' => {$modelClass}::class",
			"{$indent}" . ']);',
			'}'
		];
		$functionsBlock = implode("\n{$indent}",$functions);
		
		$framework = ucfirst( Devworx::framework() );
		$repository = ucfirst( Devworx::repositoryFolder() );
		$model = ucfirst( Devworx::modelFolder() );
		
		return implode(PHP_EOL, [
			'<?php',
			'',
			"namespace {$namespace}\\{$repository};",
			'',
			"use \\{$framework}\\{$repository}\\Abstract{$repository};",
			"use {$namespace}\\{$model}\\{$modelClass};",
			'',
			self::Comment('', "{$repository} for table {$table}", "Represents an interface to {$table} models"),
			'',
			"class {$modelClass}{$repository} extends Abstract{$repository}",
			'{',
			"{$indent}{$functionsBlock}",
			'}',
			'',
			'?>',
		]);
	}
	
	public static function Action(
		string $name,
		bool $json,
		string $table,
		string $pk = 'uid',
		string $indent = "\t"
	) {
		$header = ($json ? '$this->setBlockLayout(true)' : '');

		$result = match ($name) {
			'list' => [
				$header,
				"{$indent}" . '$list = $this->repository->findAll();',
				"{$indent}" . '$this->view->assign(\"list\", $list);'
			],

			'show' => [
				$header,
				"{$indent}" . '$uid = $this->request->getArgument(\"{$pk}\") ?? 0;',
				"{$indent}" . "if( empty(\$uid) ) Frontend::redirect(\"{$table}\", \"list\");",
				"{$indent}" . '$item = $this->repository->findByUid($uid);',
				"{$indent}" . '$this->view->assign(\"item\", $item);'
			],

			'edit' => [
				$header,
				"{$indent}" . '$itemUid = $this->request->getArgument(\"' . $pk . '\") ?? 0;',
				"{$indent}" . "if( empty(\$itemUid) ) Frontend::redirect(\"{$table}\", \"list\");",
				"{$indent}" . '$item = $this->repository->findByUid($itemUid);',
				"{$indent}" . '$this->view->assign(\"item\", $item);'
			],

			'update' => [
				$header,
				"{$indent}" . '$item = $this->request->getArgument(\"' . $table . '\");',
				"{$indent}" . "if( empty(\$item) || !is_array(\$item) ) return;",
				"{$indent}" . "if( array_key_exists(\"{$pk}\", \$item) )",
				"{$indent}{$indent}" . '$this->repository->put($item);',
				"{$indent}" . "else",
				"{$indent}{$indent}" . "\$item[\"{$pk}\"] = \$this->repository->add(\$item);",
				"{$indent}" . '$this->view->assign(\"item\", $item);'
			],

			'delete' => [
				$header,
				"{$indent}" . '$itemUid = $this->request->getArgument(\"' . $pk . '\") ?? 0;',
				"{$indent}" . "if( empty(\$itemUid) ) Frontend::redirect(\"{$table}\", \"list\");",
				"{$indent}" . '$this->repository->remove($itemUid);'
			],

			default => ["{$indent}"]
		};
		
		return [
			self::Comment('', "The {$name} action"),
			"public function {$name}Action() {",
			...$result,
			"}"
		];
	}
	
	/**
	 * Creates a PHP controller definition for a given SQL table
	 *
	 * @param string $namespace The namespace of the new model
	 * @param string $table The SQL table for the underlying data
	 * @param array|null $actions A list of actions
	 * @param string $pk The primary key of the SQL table
	 * @param string $indent The line indent for the model
	 * @return string
	 */
	public static function Controller(
		string $namespace, 
		string $table, 
		array $actions = null,
		string $pk='uid',
		string $indent = "\t"
	){
		$modelClass = ucfirst($table);
		$className = "{$modelClass}Controller";
		
		$hasActions = isset($actions) && !empty($actions);
		
		$properties = [];
		
		if( $hasActions ){
			$properties []= [
				self::Comment('','@var '.$modelClass.'Repository $repository The ' . $modelClass .' repository for table '.$table),
				'protected '.$modelClass.'Repository $repository;',
			];
		}
		
		$functions = [
			self::Comment('',"Initialization of every controller action"),
			"public function initialize(): void {",
			$hasActions ? ( "{$indent}" . '$this->repository = new '.$modelClass.'Repository();' ) : '',
			"}"
		];
		
		if( $hasActions ){
			foreach( $actions as $action ){
				$functions = [
					...$functions,
					'',
					...self::Action($action['name'],$table,$pk,$indent)
				];
			}
		}
		
		$propertiesBlock = implode("\n{$indent}", $properties);
		$functionsBlock = implode("\n{$indent}", $functions);
		
		$framework = Devworx::framework();
		$controller = Devworx::controllerFolder();
		$model = Devworx::modelFolder();
		$repository = Devworx::repositoryFolder();
		
		return implode(PHP_EOL, [
			'<?php',
			'',
			"namespace {$namespace}\\{$controller};",
			'',
			"use \\{$framework}\\Frontend;",
			"use \\{$framework}\\{$controller}\\Abstract{$controller};",
			($hasActions ? "use {$namespace}\\{$model}\\{$modelClass};" : ''),
			($hasActions ? "use {$namespace}\\{$repository}\\{$modelClass}{$repository};" : ''),
			'',
			self::Comment('', "Controller for table {$table}"),
			'',
			"class {$className} extends AbstractController",
			'{',
			"{$indent}{$propertiesBlock}",
			'',
			"{$indent}{$functionsBlock}",
			'}',
			'',
			'?>',
		]);
	}
	
	/**
	 * Checks for missing properties in a model
	 *
	 * @param string $fqcn
	 * @return array
	 */
	public static function AnalyzeModel(string $fqcn): array
    {
        if (!class_exists($fqcn)) {
            throw new \InvalidArgumentException("Class {$fqcn} not found.");
        }

        $class = new \ReflectionClass($fqcn);
        $table = strtolower($class->getShortName());
        $repository = new \Devworx\Repository($table);

        $existingProps = array_map(fn($p) => $p->getName(), $class->getProperties());
        $existingMethods = array_map(fn($m) => $m->getName(), $class->getMethods());

        $propertiesToAdd = [];
        $methodsToAdd = [];

        foreach ($repository->getDetails() as $name => $info) {
            if (in_array($name, Repository::SYSTEM_FIELDS)) continue;
            if (
                in_array($name, $existingProps) &&
                in_array('get' . ucfirst($name), $existingMethods) &&
                in_array('set' . ucfirst($name), $existingMethods)
            ) {
                // Property und Getter/Setter sind bereits vorhanden
                continue;
            }

            $field = self::TableToClass($info[0], $info[1]);
            self::CreateProperty($class->getShortName(), $name, $field, $propertiesToAdd, $methodsToAdd, "\t");
        }

        return [
            'properties' => $propertiesToAdd,
            'methods' => $methodsToAdd,
        ];
    }
	
	/**
	 * Checks for missing properties in a model file
	 *
	 * @param string $fqcn
	 * @param string $filePath
	 * @return bool
	 */
	public static function UpdateModel(string $fqcn, string $filePath): bool
	{
		$updates = self::AnalyzeModelUpdates($fqcn);

        if (empty($updates['properties']) && empty($updates['methods'])) {
            return false;
        }

        $codeToAdd = implode("\n  ", $updates['properties'])
            . "\n\n  "
            . implode("\n  ", $updates['methods'])
            . "\n";

        $code = file_get_contents($filePath);
        $pos = strrpos($code, '}');
        if ($pos === false) {
            throw new \RuntimeException("Ungültige Model-Datei: Keine schließende Klammer gefunden.");
        }

        $newCode = substr_replace($code, $codeToAdd . "}", $pos, 1);
        file_put_contents($filePath, $newCode);

        return true;
	}
	
	/**
	 * Checks all models for missing properties
	 *
	 * @param string $namespace The model namespace
	 * @param array $tables optional table list
	 * @return void
	 */
	public static function CheckModels(string $context='',array $tables=null): void {
		if( is_null($tables) || empty($tables) ){
			$tables = Database::tables();
		}

		if( empty($context) ) $context = Devworx::context();
		$context = ucfirst($context);

		$modelFolder = Devworx::modelFolder();
		$classesFolder = Devworx::classesFolder();

		foreach($tables as $table) {
			$className = ucfirst($table);
			$fileName = \Devworx\Frontend::path( $classesFolder, $namespace, $modelFolder, "{$className}.php");
			$fqcn = "{$context}\\{$modelFolder}\\{$className}";
			if (file_exists($fileName)){
				if( self::UpdateModel( $fqcn, $fileName ) )
					echo "Updated {$fqcn} from {$table} in {$fileName}\n";
				continue;
			}
			$code = self::Model(
				"\\{$context}\\{$modelFolder}", 
				$className, 
				$table
			);
			file_put_contents($fileName, $code);
			echo "Generated {$fqcn} from {$table} in {$fileName}\n";
		}
	}
}

?>
