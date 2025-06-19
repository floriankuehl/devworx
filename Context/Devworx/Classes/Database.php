<?php

namespace Devworx;

use \PDO;
use \PDOException;
use \Devworx\Interfaces\IDatabase;
use \Devworx\Utility\DatabaseUtility;

/**
 * Class Database
 * A PDO-based database wrapper implementing IDatabase
 */
class Database implements IDatabase {

	const DEFAULT_PK = 'uid';
	const CHARSET = 'utf8mb4';

	const SYSTEM_FIELDS = ['uid', 'cruser', 'hidden', 'created', 'updated', 'deleted'];
	const SYSTEM_CONDITIONS = ['hidden = 0', 'deleted IS NULL'];

	const TYPE_MAP = [
		'varchar' => 's',
		'char' => 's',
		'text' => 's',
		'longtext' => 's',
		'int' => 'i',
		'tinyint' => 'i',
		'mediumint' => 'i',
		'bigint' => 'i',
		'float' => 'd',
		'double' => 'd',
		'decimal' => 'd',
		'date' => 's',
		'datetime' => 's',
		'timestamp' => 's',
		'enum' => 's',
	];

	/** @var PDO|null */
	private static ?PDO $pdo = null;

	/** @var array */
	private static array $credentials = [];

	/**
	 * Initialize the connection credentials
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 * @param bool $connect Automatically connect
	 * @return bool
	 * @throws PDOException
	 */
	public static function initialize(string $host, string $user, string $password, string $database = '', bool $connect = true): bool {
		self::$credentials = [$host, $user, $password, $database];
		return $connect ? self::connect() : true;
	}

	/**
	 * Returns the PDO options
	 *
	 * @return array
	 */
	public static function options(): array {
		return [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];
	}

	/**
	 * Connects to the database
	 *
	 * @return bool
	 * @throws PDOException
	 */
	public static function connect(): bool {
		[$host, $user, $pass, $db] = self::$credentials;
		$dsn = "mysql:host=$host;dbname=$db;charset=" . self::CHARSET;
		self::$pdo = new PDO($dsn, $user, $pass, self::options());
		return true;
	}

	/**
	 * Disconnects the PDO instance
	 *
	 * @return bool
	 */
	public static function disconnect(): bool {
		self::$pdo = null;
		return is_null(self::$pdo);
	}

	/**
	 * Checks if connected
	 *
	 * @return bool
	 */
	public static function connected(): bool {
		return self::$pdo instanceof PDO;
	}

	/**
	 * Returns the last error message
	 *
	 * @return string|null
	 */
	public static function error(): ?string {
		return self::$pdo?->errorInfo()[2] ?? null;
	}

	/**
	 * Returns the last inserted ID
	 *
	 * @return int|null
	 */
	public static function insertID(): ?int {
		return self::$pdo ? (int) self::$pdo->lastInsertId() : null;
	}

	/**
	 * Executes a raw query
	 *
	 * @param string $query
	 * @return bool|int
	 * @throws PDOException
	 */
	public static function execute(string $query): bool|int {
		return self::$pdo->exec($query);
	}

	/**
	 * Executes a query and fetches all results
	 *
	 * @param string $query
	 * @param bool $one Fetch only one row
	 * @return mixed
	 * @throws PDOException
	 */
	public static function query(string $query, bool $one = false): mixed {
		$stmt = self::$pdo->query($query);
		return $one ? $stmt->fetch() : $stmt->fetchAll();
	}

	/**
	 * Prepares and executes a prepared statement
	 *
	 * @param string $query
	 * @param array $params
	 * @param bool $one
	 * @return mixed
	 * @throws PDOException
	 */
	public static function prepare(string $query, array $params = [], bool $one = false): mixed {
		$stmt = self::$pdo->prepare($query);
		$result = $stmt->execute($params);
		$fetch = $one ? $stmt->fetch() : $stmt->fetchAll();
		return $result ? ( $fetch === false ? $result : $fetch ) : $result;
	}

	/**
	 * Returns an escaped string for manual queries (not recommended)
	 *
	 * @param string $value
	 * @return string
	 */
	public static function escape(string $value): string {
		return self::$pdo ? self::$pdo->quote($value) : '';
	}

	/**
	 * Get all table names
	 *
	 * @return array
	 */
	public static function tables(): array {
		return array_column(self::query("SHOW TABLES"), array_keys(self::query("SHOW TABLES", true))[0]);
	}

	/**
	 * Get column structure for table
	 *
	 * @param string $table
	 * @return array
	 */
	public static function explain(string $table): array {
		return self::query("EXPLAIN `$table`");
	}

	/**
	 * Returns the primary key of a table
	 *
	 * @param string $table
	 * @return string|null
	 */
	public static function pk(string $table): ?string {
		$query = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_KEY = 'PRI'";
		$result = self::prepare($query, [$table], true);
		return $result['COLUMN_NAME'] ?? null;
	}
	
	/**
	 * Checks if the given table's field is marked as primary key
	 * Asks MySQLi information_schema
	 *
	 * @param string $table the given table
	 * @param string $field the given field
	 * @return bool
	 */
	public static function pkIs(string $table,string $field): bool {
		if( empty($table) || empty($field) )  
			return false;
		$result = self::prepare(
			"SELECT EXISTS( SELECT 1 FROM information_schema.columns WHERE TABLE_NAME = ? AND COLUMN_NAME = ? AND column_key = ?) AS hasPK;",
			[$table,$field,'PRI'],
			true
		);
		return intval($result['hasPK']) > 0;
	}

	/**
	 * Returns structured repository data for a table
	 *
	 * @param string $table
	 * @return array
	 */
	public static function repository(string $table): array {
		$meta = self::explain($table);
		$fields = $types = $placeholders = $details = [];
		$pk = self::pk($table) ?? self::DEFAULT_PK;

		foreach ($meta as $column) {
			preg_match('/^(\w+)(?:\((\d+)\))?/', $column['Type'], $match);
			$type = $match[1] ?? 'varchar';
			$details[$column['Field']] = [$type, (int)($match[2] ?? 0), self::TYPE_MAP[$type] ?? 's'];
			$fields[] = $column['Field'];
			$types[] = self::TYPE_MAP[$type] ?? 's';
			$placeholders[] = '?';
		}

		return [
			'table' => $table,
			'pk' => $pk,
			'details' => $details,
			'fields' => $fields,
			'types' => implode('', $types),
			'placeholders' => implode(',', $placeholders),
		];
	}

	/**
	 * Add a row to the table
	 *
	 * @param string $table
	 * @param array $data
	 * @return int Last insert ID
	 */
	public static function add(string $table, array $data): int {
		$fields = array_keys($data);
		$placeholders = implode(',', array_fill(0, count($fields), '?'));
		$query = "INSERT INTO `$table` (" . implode(',', $fields) . ") VALUES ($placeholders)";
		self::prepare($query, array_values($data));
		return self::insertID();
	}

	/**
	 * Update a row in the table
	 *
	 * @param string $table
	 * @param string $pk
	 * @param int $uid
	 * @param array $data
	 * @return bool
	 */
	public static function put(string $table, string $pk, int $uid, array $data): bool {
		$set = implode(', ', array_map(fn($f) => "$f = ?", array_keys($data)));
		$query = "UPDATE `$table` SET $set WHERE `$pk` = ? LIMIT 1";
		$params = array_values($data);
		$params[] = $uid;
		self::prepare($query, $params);
		return true;
	}

	/**
	 * Remove a row from the table
	 *
	 * @param string $table
	 * @param string $pk
	 * @param int $uid
	 * @return bool
	 */
	public static function remove(string $table, string $pk, int $uid): bool {
		$query = "DELETE FROM `$table` WHERE `$pk` = ? LIMIT 1";
		self::prepare($query, [$uid]);
		return true;
	}

	/**
	 * Get a row or all rows with optional system conditions
	 *
	 * @param string $table
	 * @param string $pk
	 * @param int|null $uid
	 * @param bool $withConditions
	 * @return array|null
	 */
	public static function get(string $table, string $pk, ?int $uid = null, bool $withConditions = true): ?array {
		$conds = $withConditions ? self::SYSTEM_CONDITIONS : [];
		if ($uid !== null) $conds[] = "$pk = ?";
		$where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';
		$query = "SELECT * FROM `$table` $where";
		return self::prepare($query, $uid !== null ? [$uid] : [], $uid !== null);
	}
	
	/**
	 * Returns the system conditions
	 *
	 * @return array
	 */
	public static function conditions(): array {
		return self::SYSTEM_CONDITIONS;
	}

	/**
	 * Check if a field is a system field
	 *
	 * @param string $field
	 * @return bool
	 */
	public static function isSystemField(string $field): bool {
		return in_array($field, self::SYSTEM_FIELDS, true);
	}

	/**
	 * Get system fields as array or string
	 *
	 * @param bool $asString
	 * @return array|string
	 */
	public static function getSystemFields(bool $asString = false): array|string {
		return $asString ? implode(',', self::SYSTEM_FIELDS) : self::SYSTEM_FIELDS;
	}

	/**
	 * Get system conditions as array or string
	 *
	 * @param bool $asString
	 * @return array|string
	 */
	public static function getSystemConditions(bool $asString = false): array|string {
		return $asString ? implode(' AND ', self::SYSTEM_CONDITIONS) : self::SYSTEM_CONDITIONS;
	}
}