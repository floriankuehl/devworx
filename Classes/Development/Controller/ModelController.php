<?php

namespace Development\Controller;

use \Devworx\Frontend;
use \Devworx\Utility\BuildUtility;

class ModelController extends \Devworx\AbstractController {
	
	const PHPTYPES = [
		'varchar' => 'string',
		'text' => 'string',
		'int' => 'int',
		'bigint' => 'int',
		'float' => 'float',
		'tinyint' => 'byte',
		'tinyint(1)' => 'bool',
		'timestamp' => 'DateTime',
		'datetime' => 'DateTime',
		'date' => 'DateTime',
		'time' => 'DateTime'
	];
	
	const SYSTEMFIELDS = [
		'uid','cruser','created','updated','deleted','hidden'
	];
	
	public function initialize(): void {
		
	}
	
	protected function getActions(string $className): array {
		$refClass = new \ReflectionClass($className);
		$methods = $refClass->getMethods(\ReflectionMethod::IS_PUBLIC);

		$actions = [];
		foreach ($methods as $method) {
			if( preg_match('/^([a-zA-Z0-9_]+)Action$/', $method->name, $matches) ){
				$actions[] = $matches[1];
			}
		}

		return $actions;
	}
	
	protected function getSchema(string $tableName): array {
		global $DB;
		return array_map(
			function($field){
				$type = $field['Type'];
				$pattern = '~([a-zA-Z]{1,})\((\d{1,})\)~';
				$length = 0;
				$found = preg_match($pattern,$type,$matches);
				if( !empty($found) ){
					$type = $matches[1];
					$length = intval($matches[2]);
					unset($matches);
				}
				
				$phpType = '';
				if( array_key_exists($field['Type'],self::PHPTYPES) ){
					$phpType = self::PHPTYPES[$field['Type']];
				} else if( array_key_exists($type,self::PHPTYPES) ){
					$phpType = self::PHPTYPES[$type];
				}
										
				return [
					'name' => $field['Field'],
					'key' => $field['Key'],
					'type' => $field['Type'],
					'phptype' => $phpType,
					'dbtype' => $type,
					'nullable' => $field['Null'] == 'YES',
					'value' => $field['Default'],
					'length' => $length,
					'extra' => $field['Extra'],
					'system' => in_array($field['Field'],self::SYSTEMFIELDS,true),
				];
			},
			$DB->explain($tableName)
		);
	}
	
	protected function getExistence(array $array){
		if( array_key_exists('file',$array) )
			$array['fileExists'] = is_dir($array['file']) || file_exists( $array['file'] );
		if( array_key_exists('class',$array) )
			$array['classExists'] = class_exists( $array['class'] );
		return $array;
	}
	
	protected function getInfo(string $namespace,string $table){
		
		$controllerFolder = Frontend::path( 'Classes', $namespace, 'Controller' );
		$modelFolder = Frontend::path( 'Classes', $namespace, 'Models' );
		$repositoryFolder = Frontend::path( 'Classes', $namespace, 'Repository' );
		$templateFolder = Frontend::path( 'Resources', $namespace, 'Templates' );
		
		$modelClass = ucfirst($table);
		$controllerClass = "{$modelClass}Controller";
		$repositoryClass = "{$modelClass}Repository";
		
		return [
			'name' => $table,
			'model' => $this->getExistence([
				'short' => $modelClass,
				'class' => "{$namespace}\\Models\\{$modelClass}",
				'file' => "{$modelFolder}/{$modelClass}.php",
			]),
			'controller' => $this->getExistence([
				'short' => $controllerClass,
				'class' => "{$namespace}\\Controller\\{$controllerClass}",
				'file' => "{$controllerFolder}/{$controllerClass}.php",
			]),
			'repository' => $this->getExistence([
				'short' => $repositoryClass,
				'class' => "{$namespace}\\Repository\\{$repositoryClass}",
				'file' => "{$repositoryFolder}/{$repositoryClass}.php",
			]),
			'template' => $this->getExistence([
				'file' => "{$templateFolder}/{$modelClass}"
			]),
			'properties' => $this->getSchema($table),
			'relations' => []
		];
	}
	
	protected function handleInfo(array $info,string $code,bool $create=true){
		if( 
			!(
				$info['fileExists'] &&
				$info['classExists']
			)
		){
			if( $create && !empty($code) ){
				file_put_contents($info['file'],$code);
				unset($code);
			}
			$info['fileExists'] = file_exists($info['file']);
			$info['fileCreated'] = $info['fileExists'];
			if( $info['fileCreated'] )
				$info['classExists'] = class_exists( $info['class'] );
		}
		return $info;
	}
	
	protected function handleFolderInfo(array $info,array $files,bool $create=true){
		if( $create && !$info['fileExists'] ){
			mkdir($info['file'],0x777,true);
			$info['fileExists'] = is_dir($info['file']);
		}
		
		$actionNames = array_map(fn($action)=>ucfirst($action['name']),$files);
		
		if( $info['fileExists'] ){
			$actionFiles = array_map(
				fn($file)=>implode('/',[ $info['file'], "{$file}.php" ]),
				$actionNames
			);
			$info['missing'] = array_filter(
				$actionFiles,
				fn($file)=>!file_exists($file)
			);
			$info['created'] = [];
			foreach( $info['missing'] as $file ){
				if( $create ){
					file_put_contents($file,'<?= ?>');
					$info['created'][]= $file;
				}
			}
		}
		
		return $info;
	}
	
	public function checkTable(string $namespace,string $tableName,array $info, bool $create=false){
		$table = $this->getInfo($namespace,$tableName);
		$actions = array_map(
			fn($action)=>lcfirst($action['name']),
			$info['actions'] ?? []
		);
		
		$table['controller'] = $this->handleInfo(
			$table['controller'],
			BuildUtility::Controller(
				$namespace,
				$tableName,
				$actions
			),
			$create
		);
		
		if( $table['controller']['classExists'] ){
			
			$controllerActions = $this->getActions($table['controller']['class']);
			
			//check actions
			if( empty($actions) ){
				$actions = $controllerActions;
			} else {
				$actions = array_filter(
					$actions,
					fn($action) => !in_array($controllerActions)
				);
			}
		}
		
		$table['actions'] = $actions;
				
		$table['model'] = $this->handleInfo(
			$table['model'],
			BuildUtility::Model(
				$namespace,
				$table['model']['short'],
				$tableName
			),
			$create
		);
		
		$table['repository'] = $this->handleInfo(
			$table['repository'],
			BuildUtility::Repository(
				$namespace,
				$tableName
			),
			$create
		);
		
		$table['template'] = $this->handleFolderInfo(
			$table['template'],
			$info['actions'] ?? [],
			$create
		);
		
		return $table;
	}
	
	//--------- ACTIONS
	
	public function indexAction(){
		
	}
	
	public function editorAction(){
		global $DB;
		
		$create = false;
		$namespace = $this->request->getArgument('namespace') ?? 'Frontend';
		
		$names = $DB->tables();
		
		$tables = [];
		foreach( $names as $name ){
			$tables[$name] = $this->checkTable($namespace,$name,[],$create);
		}
		
		$this->view->assign('tables', $tables);
	}
	
	public function schemaAction(){
		
		global $DB;
		
		$this->setBlockLayout(true);
		
		$create = false;
		$namespace = $this->request->getArgument('namespace') ?? 'Frontend';
		
		if( $this->request->hasArgument('table') ){
			$list = $this->request->getArgument('table');
			if( empty($list) ) 
				$list = [];
			if( is_string($list) )
				$list = [$list];
		} else
			$list = $DB->tables();
		
		$tables = [];
		foreach( $list as $table ){
			$tables[$table] = $this->checkTable($namespace,$table,[],$create);
		}
										
		$this->view->assign('tables', $tables);
	}
	
	public function checkAction(){
		$this->setBlockLayout(true);
		$input = $this->request->getJson();
		
		if( !is_array($input) )
			return;
		
		$namespace = $this->request->getArgument('namespace') ?? 'Frontend';
		$create = false;

		foreach( $input as $tableName => $schema ){
			$input[$tableName] = $this->checkTable($namespace,$tableName,$schema,$create);
		}
			
		$this->view->assign('result',$input);
	}
	
	
	
}