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
}

?>