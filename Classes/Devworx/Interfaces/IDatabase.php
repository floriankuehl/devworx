<?php

namespace Devworx\Interfaces;

/**
 * An interface for databases
 */
interface IDatabase {
	
	/**
	 * Returns the system conditions
	 *
	 * @return array
	 */
	function conditions(): array;

	/**
	 * Connects to a database
	 *
	 * @return bool
	 */
	function connect(): bool;
	
	/**
	 * Disconnects from a database
	 *
	 * @return bool
	 */
	function disconnect(): bool;
	
	/**
	 * Checks if a database is connected
	 *
	 * @return bool
	 */
	function connected(): bool;
	
	/**
	 * Returns the last error message
	 *
	 * @return string $errorMessage
	 */
	function error(): string;
	
	/**
	 * Returns the result object of a query
	 * 
	 * @param string $query The SQL query string 
	 * @return mixed
	 */
	function result(string $query): mixed;
	
	/**
	 * Returns the result of a prepared statement query
	 *
	 * @param string $query The query string
	 * @param string $format The field format string
	 * @param array $values The provided values
	 * @return mixed
	 */
	function statement(string $query,string $format,array $values): mixed;
	
	/**
	 * Returns a prepared statement
	 *
	 * @param string $query The query string
	 * @param string $format The field format string
	 * @param array $values The provided values
	 * @return mixed
	 */
	function prepare(string $query,string $format,array $values): mixed;
	
	/**
	 * Fetches data by query
	 *
	 * @param string $query The query string
	 * @param bool $one Determines if a list is fetched or a single value
	 * @param int $mode The result mode flag
	 * @return mixed
	 */
	function query(string $query,bool $one,int $mode): mixed;
	
	/**
	 * Returns the last inserted id
	 *
	 * @return ?int
	 */
	function insertID(): ?int;
	
	/**
	 * Escapes a string by the database handle
	 *
	 * @param string $value The value to escape
	 * @return string
	 */
	function escape(string $value): string;
	
	/**
	 * Gets a single database row by the primary key
	 *
	 * @param string $table The database table
	 * @param string $pk The name of the primary key
	 * @param string|null $uid The value of the primary key
	 * @param bool $conditions A flag to use the system conditions
	 * @return array|null
	 */
	function get(string $table,string $pk,string $uid=null,bool $conditions=true): ?array;
	
	/**
	 * Adds a single row to the database and returns the last inserted id
	 *
	 * @param string $table The database table
	 * @param array $data The values of the row
	 * @return int
	 */
	function add(string $table,array $data): int;
	
	/**
	 * Updates a single row of the database
	 *
	 * @param string $table The database table
	 * @param string $pk The primary key name of the table
	 * @param string $uid The value of the primary key
	 * @param array $data The values of the row
	 * @return bool
	 */
	function put(string $table,string $pk,int $uid,array $data): bool;
	
	/**
	 * Removes a single row of the database by pk and uid
	 *
	 * @param string $table The database table
	 * @param string $pk The primary key name of the table
	 * @param string $uid The value of the primary key
	 * @return bool
	 */
	function remove(string $table,string $pk,int $uid): bool;
}

?>