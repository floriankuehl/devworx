<?php

namespace Devworx;

use \Devworx\Interfaces\IDatabase;

/**
 * The class for Databases
 */
class Database implements IDatabase {
  
	const CHARSET = "utf8mb4";

	/** @var array The database credentials */
	protected $credentials = null;
	/** @var \mysqli The database connection handle */
	protected $connection = null;

	function __construct(
		string $host,
		string $user,
		string $password,
		string $database
	){
		$this->credentials = [
		  $host, 
		  $user, 
		  $password, 
		  $database
		];
	}

	/**
	 * Returns the system conditions for active rows
	 * 
	 * @return array
	 */
	function conditions(): array {
		//TODO: Move to constants, like in Repository
		return [
		  "( hidden = 0 )",
		  "ISNULL(deleted)",
		];
	}

	/**
	 * Connects to the database
	 * 
	 * @return bool
	 */
	function connect(): bool {
		if( isset( $this->connection ) )
		  return $this->connection->ping();
		$this->connection = new \mysqli(...$this->credentials);
		$this->connection->set_charset(self::CHARSET);
		return isset($this->connection);
	}

	/**
	 * Disconnects from the database
	 * 
	 * @return bool
	 */
	function disconnect(): bool {
		if( isset( $this->connection ) )
		  return $this->connection->close();
		return true;
	}

	/**
	 * Checks if the database is connected
	 * 
	 * @return bool
	 */
	function connected(): bool {
		return isset($this->connection);
	}

	/**
	 * Returns the current database error
	 * 
	 * @return string
	 */
	function error(): string {
		return $this->connection->error;
	}

	/**
	 * Returns the result of a query
	 * 
	 * @param string $query The SQL query string 
	 * @return object
	 */
	function result(string $query){
		return $this->connection->query($query);
	}

	/**
	 * Returns the result of a query based on $one and $mode
	 *
	 * @param string $query The SQL query string 
	 * @param bool $one A flag to differentiate between fetch and fetch_all
	 * @param int $mode The mysqli result mode
	 * @return mixed
	 */
	function query(string $query,bool $one=false,int $mode=MYSQLI_NUM){
		$result = $this->result($query);
		if( $result === false )
		  throw new \Exception($this->error());
		if( $result === true )
		  return $result;
		return $one ? 
		  $result->fetch_array($mode) : 
		  $result->fetch_all($mode);
	}

	/**
	 * Returns a prepared MySQL statement
	 *
	 * @param string $query The SQL query string 
	 * @param string $format The MySQL field format string 
	 * @param array $values The field values for the placeholders
	 * @return object
	 */
	function statement(string $query,string $format,array $values){
		$connection = $this->connection;
		$stmt = $connection->prepare($query);

		$values = array_map(function($value) use ($connection){
		  return $connection->real_escape_string($value);
		},$values);

		$stmt->bind_param($format, ...$values);

		return $stmt;
	}

	/**
	 * Returns the result of a prepared MySQL statement
	 *
	 * @param string $query The SQL query string 
	 * @param string $format The MySQL field format string 
	 * @param array $values The field values for the placeholders
	 * @return mixed
	 */
	function prepare(string $query,string $format,array $values){
		$stmt = $this->statement($query,$format,$values);
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();

		if( is_bool($result) ) 
		  return $result;

		$rows = [];
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		  $rows []= $row;
		}

		return $rows;
	}

	/**
	 * Returns the last inserted id
	 *
	 * @return int|null
	 */
	function insertID(): ?int {
		return intval($this->connection->insert_id);
	}

	/**
	 * Escapes a given string with the help of the connection
	 *
	 * @param string $value The given string
	 * @return string
	 */
	function escape(string $value): string {
		return $this->connection->real_escape_string($value);
	}

	/**
	 * Gets a single database row by the primary key
	 *
	 * @param string $table The database table
	 * @param string $pk The name of the primary key
	 * @param string|null $uid The value of the primary key
	 * @param bool $conditions A flag to use the system conditions
	 * @return array|null
	 */
	function get(string $table,string $pk,string $uid=null,bool $conditions=true): ?array {
		$one = isset($uid) && !empty($uid);
		$conditions = $conditions ? self::conditions() : [];
		if( $one )
		  $conditions []= "( {$pk} = {$uid} )";
		$conditions = implode(" AND ",$conditions);
		return $this->query("SELECT * FROM {$table} WHERE {$conditions};",$one,MYSQLI_ASSOC);
	}

	/**
	 * Adds a single row to the database and returns the last inserted id
	 *
	 * @param string $table The database table
	 * @param array $data The values of the row
	 * @return int
	 */
	function add(string $table,array $data): int {
		$fields = array_map([$this,'escape'], array_keys($data) );
		$values = array_map( function($v){ return "'{$v}'"; }, array_values($data) );
		$fields = implode(",",$fields);
		$values = implode(",",$values);
		$result = $this->result("INSERT INTO {$table} ({$fields}) VALUES ({$values});");
		return $result == FALSE ? 0 : $this->insertID();
	}

	/**
	 * Updates a single row of the database
	 *
	 * @param string $table The database table
	 * @param string $pk The primary key name of the table
	 * @param string $uid The value of the primary key
	 * @param array $data The values of the row
	 * @return bool
	 */
	function put(string $table,string $pk,int $uid,array $data): bool {
		$fields = [];
		foreach( $data as $k => $v ){
		  $k = $this->escape($k);
		  $v = $this->escape($v);
		  $fields []= "{$k} = '{$v}'";
		}
		$fields = implode(',',$fields);
		return (bool) $this->result("UPDATE {$table} SET {$fields} WHERE ({$pk} = '{$uid}') LIMIT 1;");
	}

	/**
	 * Removes a single row of the database by pk and uid
	 *
	 * @param string $table The database table
	 * @param string $pk The primary key name of the table
	 * @param string $uid The value of the primary key
	 * @return bool
	 */
	function remove(string $table,string $pk,int $uid): bool {
		return (bool) $this->result("DELETE FROM {$table} WHERE ({$pk} = '{$uid}') LIMIT 1;");
	}

}
