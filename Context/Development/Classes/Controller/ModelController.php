<?php

namespace Development\Controller;

use \Devworx\Database;
use \Devworx\Frontend;
use \Devworx\Devworx;
use \Devworx\Configuration;
use \Devworx\Redirect;
use \Devworx\Utility\PathUtility;
use \Devworx\Utility\BuildUtility;

class ModelController extends \Devworx\Controller\AbstractController {
	
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
		'uid','cruser',
		'created','updated','deleted',
		'hidden'
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
			Database::explain($tableName)
		);
	}
	
	protected function getExistence(array $array){
		if( array_key_exists('file',$array) )
			$array['fileExists'] = is_dir($array['file']) || file_exists( $array['file'] );
		if( array_key_exists('class',$array) )
			$array['classExists'] = class_exists( $array['class'] );
		return $array;
	}
	
	protected function getInfo(string $context,string $table,bool $schema=true){
		
		$controllerFolder = PathUtility::context( $context, 'Classes', Devworx::controllerFolder() );
		$modelFolder = PathUtility::context( $context, 'Classes', Devworx::modelFolder() );
		$repositoryFolder = PathUtility::context( $context, 'Classes', Devworx::repositoryFolder() );
		$templateFolder = PathUtility::context( $context, 'Resources', 'Private', 'Templates' );
		
		$hasController = is_dir($controllerFolder);
		$hasModel = is_dir($modelFolder);
		$hasRepository = is_dir($repositoryFolder);
		$hasTemplate = is_dir($templateFolder);
		
		$modelClass = ucfirst($table);
		$controllerClass = "{$modelClass}Controller";
		$repositoryClass = "{$modelClass}Repository";
		
		
		
		return [
			'name' => $table,
			'model' => $hasModel ? $this->getExistence([
				'short' => $modelClass,
				'class' => "{$context}\\Model\\{$modelClass}",
				'file' => "{$modelFolder}/{$modelClass}.php",
			]) : false,
			'controller' => $hasController ? $this->getExistence([
				'short' => $controllerClass,
				'class' => "{$context}\\Controller\\{$controllerClass}",
				'file' => "{$controllerFolder}/{$controllerClass}.php",
			]) : false,
			'repository' => $hasRepository ? $this->getExistence([
				'short' => $repositoryClass,
				'class' => "{$context}\\Repository\\{$repositoryClass}",
				'file' => "{$repositoryFolder}/{$repositoryClass}.php",
			]) : false,
			'template' => $hasTemplate ? $this->getExistence([
				'file' => "{$templateFolder}/{$modelClass}"
			]) : false,
			'properties' => $schema ? $this->getSchema($table) : [],
			'relations' => []
		];
	}
	
	protected function handleInfo(array $info,string $code='',bool $create=true): array {
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
	
	protected function handleFolderInfo(array $info,array $files,bool $create=true): array {
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
	
	private function generateControllerCode(string $context,string $table,array $actions=null){
		if( $actions === null ) $actions = [];
		return BuildUtility::Controller(
			$context,
			$table,
			$actions
		);
	}
	
	public function checkTable(string $context,string $tableName,array $info, bool $create=false){
		$table = $this->getInfo($context,$tableName);
		
		$actions = array_map(
			fn($action)=>lcfirst($action['name']),
			$info['actions'] ?? []
		);
		
		if( is_array($table['controller']) ){
			$table['controller'] = $this->handleInfo(
				$table['controller'],
				$this->generateControllerCode($context,$tableName,$actions),
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
		}
		$table['actions'] = $actions;
		
		if( is_array($table['model']) ){
			$table['model'] = $this->handleInfo(
				$table['model'],
				BuildUtility::Model(
					$context,
					$table['model']['short'],
					$tableName
				),
				$create
			);
		}
		
		if( is_array($table['repository']) ){
			$table['repository'] = $this->handleInfo(
				$table['repository'],
				BuildUtility::Repository(
					$context,
					$tableName
				),
				$create
			);
		}
		
		if( is_array($table['template']) ){
			$table['template'] = $this->handleFolderInfo(
				$table['template'],
				$info['actions'] ?? [],
				$create
			);
		}
		
		return $table;
	}
	
	//--------- ACTIONS
	
	public function indexAction(){
		
	}
	
	public function editorAction(){
		$create = false;
		
		$names = Database::tables();
		
		$tables = [];
		foreach( Devworx::contexts() as $context ){
			foreach( $names as $name ){
				$tables[$context][$name] = $this->checkTable($context,$name,[],$create);
			}
		}
		$this->view->assign('tables', $tables);
	}
	
	public function schemaAction(){
		
		$this->setBlockLayout(true);
		$this->setBlockRendering(true);
		
		$create = false;		
		if( $this->request->hasArgument('table') ){
			$list = $this->request->getArgument('table');
			if( empty($list) ) 
				$list = [];
			if( is_string($list) )
				$list = [$list];
		} else
			$list = Database::tables();
		
		$context = $this->request->getArgument('context') ?? Devworx::context();
		
		$tables = [];
		foreach( $list as $table ){
			$tables[$table] = $this->checkTable($context,$table,[],$create);
		}
		
		echo json_encode($tables,JSON_PRETTY_PRINT);
		//$this->view->assign('tables', $tables);
	}
	
	public function checkAction(){
		$this->setBlockLayout(true);
		$input = $this->request->getJson();
		
		if( !is_array($input) )
			return;
		
		$create = false;
		foreach( Devworx::contexts() as $context ){
			foreach( $input as $table => $schema ){
				$input[$context][$table] = $this->checkTable($context,$tableName,$schema,$create);
			}
		}
		$this->view->assign('result',$input);
	}
	
	
	
}