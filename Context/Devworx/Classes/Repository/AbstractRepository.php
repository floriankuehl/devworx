<?php

namespace Devworx\Repository;

use \Devworx\Database;
use \Devworx\Interfaces\IRepository;
use \Devworx\Utility\ModelUtility;
use \Devworx\Utility\PathUtility;
use \Devworx\Caches;

abstract class AbstractRepository implements IRepository {
  
	/** @var string The current table */
	protected $table = '';
	/** @var string $namespace The namespace of the repository */
	protected $namespace = '';
	/** @var string $pk The primary key of the table */
	protected $pk = '';
	/** @var array $details The field details for the table */
	protected $details = [];
	/** @var array $fields The field list for the table */
	protected $fields = [];
	/** @var array $types The type list of the table */
	protected $types = [];
	/** @var array $placeholders The type placeholders of the table */
	protected $placeholders = [];
	/** @var string The class to map after fetching */
	protected $mapResult = '';
	/** @var array The default conditions for this repository */
	protected $conditions = [];

	/** 
	 * The constructor for Repositories
	 *
	 * @param string|array $values table name or attribute array
	 * @param string|null $className if null and not provided in $values, a model class will be guessed based on namespace
	 */
	public function __construct($values,string $className=null){
		
		if(!empty($values)){
			if( is_string($values) ){
				$this->table = $values;
			} elseif( is_array($values) ){
				$this->fromArray($values);
			}
		}

		if( is_null($this->details) || empty($this->details) ){
			$class = explode('\\',get_class($this));
			$context = $class[0];

			$cache = Caches::get('Repository');
			if( $cache === null ) return;
			
			if( $cache->needsUpdate($context, $this->table) )
				$cache->create($context, $this->table );
			
			$data = $cache->get( $context, $this->table );

			if( ( $data['mapResult'] ?? null ) === null ){
				$model = $context . "\\Models\\" . ucfirst($this->table);
				$data['mapResult'] = $className ?? $model;
			}
			
			if( ( $data['conditions'] ?? null ) === null ){
				$data['conditions'] = Database::SYSTEM_CONDITIONS;
			}
			
			$this->fromArray($data);
		}
	}

	/** 
	 * Getter for the table
	 *
	 * @return string
	 */
	public function getTable(): string {
		return $this->table;
	}

	/** 
	 * Getter for the set primary key 
	 *
	 * @return string
	 */
	public function getPK(): string {
		return $this->pk;
	}

	/** 
	 * Returns field details if $field is set, otherwise returns an array of all field details  
	 *
	 * @param string $field The field to check
	 * @return string|array
	 */
	public function getDetails(string $field=''): string|array {
		return empty($field) ? $this->details : $this->details[$field];
	}

	/** 
	 * Getter for the field list as an array 
	 *
	 * @return array
	 */
	public function getFields(): array {
		return $this->fields;
	}

	/** 
	 * Getter for the type list as an array 
	 *
	 * @return array
	 */
	public function getTypes(): array {
		return $this->types;
	}

	/** 
	 * Getter for the value placeholder list as an array 
	 *
	 * @return array
	 */
	public function getPlaceholders(): array {
		return $this->placeholders;
	}

	/** 
	 * Getter for class to map the row data to 
	 *
	 * @return string
	 */
	public function getMapResult(): string {
		return $this->mapResult;
	}

	/** 
	 * Setter for class to map the row data to 
	 *
	 * @param string $className The FQCN to map to
	 * @return void
	 */
	public function setMapResult(string $className): void {
		$this->mapResult = $className;
	}

	/** 
	 * Getter for the default conditions 
	 *
	 * @return array $conditions the default mysql conditions
	 */
	public function getConditions(): array {
		return $this->conditions;
	}
	
	/** 
	 * Setter for the default conditions 
	 *
	 * @param array $conditions a list of mysql conditions for this table
	 * @return void
	 */
	public function setConditions(array $conditions): void {
		$this->conditions = $conditions;
	}

	/** 
	 * Shortcut for explaining the table
	 *
	 * @return string
	 */
	public function explain(): array {
		return Database::explain($this->table);
	}

	/** 
	 * Returns the last error message of $db 
	 *
	 * @return string
	 */
	public function error(): string {
		return Database::error();
	}

	/** 
	 * Checks if the set table contains a primary key 
	 *
	 * @return bool
	 */
	public function hasPK(): bool {
		return Database::pkIs($this->table,$this->pk);
	}

	/** 
	 * Returns the primary key of the set table 
	 *
	 * @return string
	 */
	public function readPK(): string {
		return Database::pk($this->table);
	}

	/** 
	 * Returns the current configuration as an array 
	 *
	 * @return array
	 */
	public function toArray(): array {
		$reflection = new \ReflectionClass($this);
        $protectedProps = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);

        $result = [];
        foreach ($protectedProps as $prop) {
            $prop->setAccessible(true);
            $result[$prop->getName()] = $prop->getValue($this);
        }

        return $result;
	}

	/** 
	 * Loads the current configuration from an array 
	 *
	 * @param array $value The preset values for the repository configuration
	 * @return bool $result all keys are transfered
	 */
	public function fromArray(array $value): bool {
		$result = true;
		foreach( $value as $k => $v ){
			if( property_exists($this,$k) )
				$this->$k = $v;
			else
				$result = false;
		}
		return $result;
	}
	
	

	/** 
	 * Finds all rows of the set $table 
	 *
	 * @param string $fields The fields to select
	 * @param string $order The ordering of the result
	 * @param int $offset The result offset
	 * @param int $limit The result limit
	 * @return array
	 */
	public function findAll(string $fields='*',string $order='',int $offset=0,int $limit=0): array {
		$limit = $limit > 0 ? " LIMIT {$offset},{$limit}" : "";
		$order = " ORDER BY " . ( empty($order) ? "{$this->pk} ASC" : $order );
		$system = implode(" AND ",$this->conditions);
		$result = Database::query("SELECT {$fields} FROM {$this->table} WHERE {$system}{$order}{$limit};",false,MYSQLI_ASSOC); 
		return empty($this->mapResult) ? $result : ModelUtility::toModels( $result, $this->mapResult );
	}

	/** 
	 * Finds a subset of rows of the set $table based on $key and $value 
	 *
	 * @param string $key The key for the subquery
	 * @param mixed $value The value for the subquery
	 * @param string $fields The fields to select
	 * @param string $order The ordering of the result
	 * @param int $offset The result offset
	 * @param int $limit The result limit
	 * @return array
	 */
	public function findBy(string $key,$value,string $fields='*',string $order='',int $offset=0,int $limit=0): array {
		$limit = $limit > 0 ? " LIMIT {$offset},{$limit}" : "";
		$order = " ORDER BY " . (empty($order) ? "{$this->pk} ASC" : $order );
		$system = implode(" AND ",$this->conditions);
		$result = Database::query("SELECT {$fields} FROM {$this->table} WHERE ({$key} = '{$value}') AND {$system}{$order}{$limit};",false,MYSQLI_ASSOC);
		return empty($this->mapResult) ? $result : ModelUtility::toModels( $result, $this->mapResult );
	}

	/** 
	 * Finds a single row of the set $table based on $key and $value 
	 * 
	 * @param string $key The key for the subquery
	 * @param mixed $value The value for the subquery
	 * @param string $fields The fields to select
	 * @return array|object|null
	 */
	public function findOneBy(string $key,$value,string $fields='*'): array|object|null {
		$system = implode(" AND ",$this->conditions);
		$result = Database::query("SELECT {$fields} FROM {$this->table} WHERE ({$key} = '{$value}') AND {$system} LIMIT 1;",true,MYSQLI_ASSOC);
		return is_null($result) ? $result : ( empty($this->mapResult) ? $result : ModelUtility::toModel( $result, $this->mapResult ) );
	}

	/** 
	 * Finds a subset of rows of the set $table by a given associative $filter array 
	 *
	 * @param array $filter The key-value pair to check against
	 * @param string $fields The fields to select
	 * @param string $order The ordering of the result
	 * @param int $offset The result offset
	 * @param int $limit The result limit
	 * @return array 
	 */
	public function filter(
		array $filter,
		string $fields='*',
		string $order='',
		int $offset=0,
		int $limit=0
	): array {

		if( empty($filter) ) return [];
		$conditions = [...$this->conditions];

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

		$result = Database::query("SELECT {$fields} FROM {$this->table} WHERE {$conditions}{$order}{$limit};",false,MYSQLI_ASSOC);
		return empty($this->mapResult) ? $result : ModelUtility::toModels( $result, $this->mapResult );
	}

	/** 
	 * Counts all rows of the set $table 
	 *
	 * @return int
	 */
	public function count(): int {
		$system = implode(" AND ",$this->conditions);
		$result = Database::query("SELECT COUNT({$this->pk}) FROM {$this->table} WHERE {$system} LIMIT 1;",true);
		return $result[0];
	}

	/** 
	 * Counts rows of the set $table by $field and $value 
	 *
	 * @param string $field The field of the filter
	 * @param mixed $value The value of the filter
	 * @return int
	 */
	public function countBy(string $field,mixed $value): int {
		if( in_array($field,$this->fields) ){
			$system = implode(" AND ",$this->conditions);
			$result = Database::query("SELECT COUNT({$this->pk}) FROM {$this->table} WHERE {$field}='{$value}' AND {$system} LIMIT 1;",true);
			return $result[0];
		}
		return -1;
	}

	/** 
	 * Finds a row by the $pk of the set $table with value $uid 
	 * 
	 * @param mixed $uid The uid of the row
	 * @param string $fields The fields to read from the row
	 * @return array|object
	 */
	public function findByUid($uid,string $fields='*'): array|object {
		$system = implode(" AND ",$this->conditions);
		$result = Database::query("SELECT {$fields} FROM {$this->table} WHERE ({$this->pk} = '{$uid}') AND {$system} LIMIT 1;",true,MYSQLI_ASSOC);
		return empty($this->mapResult) ? $result : ModelUtility::toModel( $result, $this->mapResult );
	}

	/** 
	 * Adds a row to the set $table and returns the last inserted id
	 *
	 * @param array $data the data for the row
	 * @return int
	 */
	public function add(array $data): int {
		return Database::add($this->table,$data);
	}

	/** 
	 * Adds many rows to the set $table and returns a list of last inserted ids 
	 * @param array $rows the list of data rows
	 * @return array
	 */
	public function addAll(array $rows): array {
		return array_map(fn($row)=>$this->add($row),$rows);
	}

	/** 
	 * Updates a given row with $data 
	 * @param array $data the data for the row
	 * @return bool
	 */
	public function put(array $data): bool {   
		$uid = null;
		$fields = [];
		$types = [];
		$prepared = [];

		if( array_key_exists( $this->pk, $data) ){
			$uid = (int)$data[$this->pk];
				foreach( $data as $field => $value ){
				$prepared []= $value;
				$fields []= "{$field}=?";
				$types []= $this->details[$field][2];
			}
		} else {
			if( count($data) == count($this->fields) ){
				$prepared = $data;
				$uid = $data[0];
				foreach( $this->fields as $i => $field ){
					$fields []= "{$field}=?";
					$types []= $this->details[$field][2];
				}
			} else {
				throw new \Exception("Data length must match fields length");
				return false;
			}
		}

		$fields = implode(',',$fields);
		$types = implode('',$types);
		$prepared []= $uid;
		return Database::prepare(
			"UPDATE {$this->table} SET {$fields} WHERE {$this->pk} = ? AND ISNULL(deleted);",
			"{$types}i",
			$prepared
		);
	}

	/** 
	* Sets the deleted timestamp of a row by its uid(s)
	*
	* @param int|array $uid the uid(s) to remove
	* @return int
	*/
	public function remove($uid): int {
		if( is_array($uid) ){
			if( empty($uid) ) return null;
			$uid = implode(',',$uid);
			return Database::query("UPDATE {$this->table} SET deleted=CURRENT_TIMESTAMP WHERE {$this->pk} IN ({$uid});",true,MYSQLI_NUM);
		}
		return Database::query("UPDATE {$this->table} SET deleted=CURRENT_TIMESTAMP WHERE {$this->pk} = '{$uid}';",true,MYSQLI_NUM);
	}

	/** 
	* Unsets the deleted timestamp of a row by its uid(s)
	*
	* @param int|array $uid the uid(s) to remove
	* @return int
	*/
	public function recycle($uid): int {
		if( is_array($uid) ){
			if( empty($uid) ) return null;
			$uid = implode(',',$uid);
			return Database::query("UPDATE {$this->table} SET deleted=NULL WHERE {$this->pk} IN ({$uid});",true,MYSQLI_NUM);
		}
		return Database::query("UPDATE {$this->table} SET deleted=NULL WHERE {$this->pk} = '{$uid}';",true,MYSQLI_NUM);
	}

	/** 
	 * Deletes a row completely by its uid(s)
	 *
	 * @param int|array $uid the uid(s) to delete
	 * @return int
	 */
	public function delete($uid): int {
		if( is_array($uid) ){
			if( empty($uid) ) return null;
			$uid = implode(',',$uid);
			return Database::query("DELETE FROM {$this->table} WHERE {$this->pk} IN ({$uid});",true,MYSQLI_NUM);
		}
		return Database::query("DELETE FROM {$this->table} WHERE {$this->pk} = '{$uid}';",true,MYSQLI_NUM);
	}
}


?>
