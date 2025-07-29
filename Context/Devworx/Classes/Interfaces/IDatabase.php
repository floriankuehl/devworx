<?php

namespace Devworx\Interfaces;

/**
 * An interface for databases
 */
interface IDatabase {
	
	/** 
	 * Checks if $field is a system field 
	 *
	 * @param string $field The field to check
	 * @return bool
	 */
	public static function isSystemField(string $field): bool;
	
	/** 
	 * Returns the system fields as a string if $string is true, otherwise returns an array 
	 *
	 * @param bool $string Flag for converting to string or to array
	 * @return string|array
	 */
	public static function getSystemFields(bool $string=false): string|array;
	
	/** 
	 * Returns the system conditions as a string if $string is true, otherwise returns an array 
	 *
	 * @param bool $string Flag for converting to string or to array
	 * @return bool
	 */
	public static function getSystemConditions(bool $string=false): string|array;
	
	/**
	 * Returns the connection options
	 *
	 * @return array
	 */
	public static function options(): array;
	
	/**
	 * Initializes the database and allows set credentials
	 * 
	 * @param string $host the database server
	 * @param string $user the server user
	 * @param string $password the server password
	 * @param string $database optional database name
	 * @param bool $connect calls connect if database name is provided
	 * @return bool $result connected
	 */
	public static function initialize(
		string $host,
		string $user,
		string $password,
		string $database='',
		bool $connect=true
	): bool;
	
	/**
	 * Returns the system conditions
	 *
	 * @return array
	 */
	public static function conditions(): array;

	/**
	 * Connects to a database
	 *
	 * @return bool
	 */
	public static function connect(): bool;
	
	/**
	 * Disconnects from a database
	 *
	 * @return bool
	 */
	public static function disconnect(): bool;
	
	/**
	 * Checks if a database is connected
	 *
	 * @return bool
	 */
	public static function connected(): bool;
	
	/**
	 * Returns the last error message
	 *
	 * @return ?string $errorMessage
	 */
	public static function error(): ?string;
	
	/**
	 * Prepares a statement
	 *
	 * @param string $query The query string
	 * @param array $params the params for the query
	 * @param bool $one fetch single result
	 * @return mixed
	 */
	static function prepare(string $query, array $params = [], bool $one = false): mixed;
	
	/**
	 * Fetches data by query
	 *
	 * @param string $query The query string
	 * @param bool $one Determines if a list is fetched or a single value
	 * @return mixed
	 */
	static function query(string $query, bool $one = false): mixed;
	
	/**
	 * Retrieves all table names
	 *
	 * @return array|null
	 */
	public static function tables(): mixed;
	
	/**
	 * Retrieves all field informations of a table
	 *
	 * @param string $table The query string
	 * @return array|null
	 */
	public static function explain(string $table): mixed;
	
	/**
	 * Retrieves the column name of the primary key of a table
	 *
	 * @param string $table The table
	 * @return string|null
	 */
	public static function pk(string $table): mixed;
	
	/**
	 * Checks if a primary key column with name $field exists in table $table
	 *
	 * @param string $table The table
	 * @param string $field The column name
	 * @return bool
	 */
	public static function pkIs(string $table,string $field): bool;
	
	
	/**
	 * Returns the last inserted id
	 *
	 * @return ?int
	 */
	public static function insertID(): ?int;
	
	/**
	 * Escapes a string by the database handle
	 *
	 * @param string $value The value to escape
	 * @return string
	 */
	public static function escape(string $value): string;
	
	
	/**
	 * Get a row or all rows with optional system conditions
	 *
	 * @param string $table
	 * @param string $pk
	 * @param int|null $uid
	 * @param bool $withConditions
	 * @return array|null
	 */
	static function get(string $table, string $pk, ?int $uid = null, bool $withConditions = true): ?array;
	
	/**
	 * Adds a single row to the database and returns the last inserted id
	 *
	 * @param string $table The database table
	 * @param array $data The values of the row
	 * @return int
	 */
	public static function add(string $table,array $data): int;
	
	/**
	 * Updates a single row of the database
	 *
	 * @param string $table The database table
	 * @param string $pk The primary key name of the table
	 * @param string $uid The value of the primary key
	 * @param array $data The values of the row
	 * @return bool
	 */
	public static function put(string $table,string $pk,int $uid,array $data): bool;
	
	/**
	 * Removes a single row of the database by pk and uid
	 *
	 * @param string $table The database table
	 * @param string $pk The primary key name of the table
	 * @param string $uid The value of the primary key
	 * @return bool
	 */
	public static function remove(string $table,string $pk,int $uid): bool;
}

?>