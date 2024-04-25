<?php

namespace framework\database;

use framework\config\Config;
use framework\log\Log;
use Exception;
use mysqli;
use mysqli_result;
use mysqli_stmt;
use RuntimeException;

/**
 * Class DB
 *
 * A class for interacting with the database using mysqli with prepared statements for security.
 *
 * @package bs\framework\database
 */
class DB
{
	private mysqli $connection;

	/**
	 * DB constructor.
	 *
	 * Establishes a connection to the database using configuration parameters.
	 *
	 * @throws RuntimeException If connection to the database fails.
	 * @throws \Exception
	 */
	public function __construct()
	{
		$host = Config::host();
		$user = Config::user();
		$pass = Config::pass();
		$db = Config::name();

		$this->connection = new mysqli($host, $user, $pass, $db);

		if ($this->connection->connect_error) {
			Log::entry('Failed to connect to database: ' . $this->connection->connect_error);
			throw new RuntimeException('Failed to connect to database');
		}

		Log::entry('Connected to database successfully');
	}

	/**
	 * Prepares and executes an SQL query with optional parameters.
	 *
	 * @param string $sql   The SQL query to execute.
	 * @param array $params Optional parameters for prepared statement.
	 *
	 * @return mysqli_result|bool The result of the query execution.
	 */
	private function prepareAndExecute(string $sql, array $params = []): mysqli_result|bool
	{
		$stmt = $this->connection->prepare($sql);

		if (!$stmt) {
			Log::entry('Failed to prepare SQL statement: ' . $this->connection->error);

			return false;
		}

		if (!empty($params)) {
			$types = str_repeat('s', count($params)); // Assuming all values are strings (s)
			$stmt->bind_param($types, ...$params);
		}

		$stmt->execute();

		return $stmt->get_result();
	}

	#region CRUD Operations

	/**
	 * Retrieves all records from a specified table.
	 *
	 * @param string $table The name of the table.
	 *
	 * @return mysqli_result|bool The result set from the query.
	 */
	public function all(string $table): mysqli_result|bool
	{
		$sql = "SELECT * FROM $table";

		return $this->prepareAndExecute($sql);
	}

	/**
	 * Retrieves records from a specified table based on a column value.
	 *
	 * @param string $table  The name of the table.
	 * @param string $column The column name to filter on.
	 * @param string $value  The value to match in the specified column.
	 *
	 * @return mysqli_result|bool The result set from the query.
	 */
	public function filter(string $table, string $column, string $value): mysqli_result|bool
	{
		$sql = "SELECT * FROM $table WHERE $column = ?";

		return $this->prepareAndExecute($sql, [$value]);
	}

	/**
	 * Inserts a new record into the specified table.
	 *
	 * @param string $table The name of the table.
	 * @param array $data   An associative array of column names and values to insert.
	 *
	 * @return bool True if the insert operation was successful, false otherwise.
	 */
	public function insert(string $table, array $data): bool
	{
		$columns = implode(', ', array_keys($data));
		$placeholders = implode(', ', array_fill(0, count($data), '?'));

		$sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

		return $this->prepareAndExecute($sql, array_values($data)) !== false;
	}

	/**
	 * Update records in the specified table with the provided data.
	 *
	 * @param string $table The name of the table to update.
	 * @param array $data An associative array of column names and new values to update.
	 * @param string $where Optional WHERE clause to specify conditions for updating.
	 * @return bool True on success, false on failure.
	 */
	public function update(string $table, array $data, string $where = ''): bool
	{
		if (empty($table) || empty($data)) {
			return false;
		}

		// Construct the SET clause for the update query
		$setClause = [];
		foreach ($data as $column => $value) {
			$setClause[] = "$column = ?";
		}
		$setClause = implode(', ', $setClause);

		// Construct the UPDATE query
		$sql = "UPDATE $table SET $setClause";
		if (!empty($where)) {
			$sql .= " WHERE $where";
		}

		// Prepare and execute the SQL statement with bound parameters
		$stmt = $this->prepareAndBindParams($sql, $data);
		if (!$stmt) {
			return false;
		}

		// Execute the prepared statement
		$result = $stmt->execute();

		// Check if the update was successful
		if ($result) {
			Log::entry('Records updated successfully');
		} else {
			Log::entry('Failed to update records: ' . $stmt->error);
		}

		// Clean up resources
		$stmt->close();

		return $result;
	}

	/**
	 * Performs a soft delete by setting a column value to NULL in the specified table.
	 *
	 * @param string $table  The name of the table.
	 * @param string $column The column name to set to NULL.
	 *
	 * @return bool True if the soft delete operation was successful, false otherwise.
	 */
	public function softDelete(string $table, string $column): bool
	{
		$sql = "UPDATE $table SET $column = NULL";

		return $this->prepareAndExecute($sql) !== false;
	}

	/**
	 * Adds a new column to the specified table.
	 *
	 * @param string $table The name of the table.
	 * @param string $column The name of the column to add.
	 * @param string $type The data type of the new column.
	 * @return bool True if the column addition was successful, false otherwise.
	 */
	public function addColumn(string $table, string $column, string $type): bool
	{
		if (empty($table) || empty($column) || empty($type)) {
			return false;
		}

		$sql = "ALTER TABLE $table ADD $column $type";

		try {
			$stmt = $this->connection->prepare($sql);

			if (!$stmt) {
				Log::entry('Failed to prepare SQL statement: ' . $this->connection->error);
				return false;
			}

			$stmt->execute();
			$stmt->close();

			return true;
		} catch (Exception $e) {
			Log::entry('Error adding column to table: ' . $e->getMessage());
			return false;
		}
	}

	#endregion


	#region Relation Building

	/**
	 * Builds one-to-many relations (has_many) by adding foreign key constraints.
	 *
	 * @param string $parentTable The name of the parent table (e.g., 'users').
	 * @param array $entries      An associative array where keys are column names and values are child table classes.
	 *
	 * @return bool True if the relation building was successful, false otherwise.
	 */
	public function buildOneToManyRelation(string $parentTable, array $entries): bool
	{
		foreach ($entries as $column => $childClass) {
			$childTable = $this->getTableNameFromClass($childClass);

			$sql = "ALTER TABLE $childTable 
                ADD CONSTRAINT fk_$childTable" . "_$column 
                FOREIGN KEY ($column) 
                REFERENCES $parentTable(id) 
                ON DELETE CASCADE";

			if (!$this->prepareAndExecute($sql)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Builds one-to-one relations by adding foreign key constraints.
	 *
	 * @param string $table  The name of the table.
	 * @param array $entries An associative array where keys are column names and values are referenced table names.
	 *
	 * @return bool True if the relation building was successful, false otherwise.
	 */
	public function buildOneToOneRelation(string $table, array $entries): bool
	{
		foreach ($entries as $column => $entry) {
			$sql = "ALTER TABLE $table ADD FOREIGN KEY ($column) REFERENCES $entry($column)";
			if (!$this->prepareAndExecute($sql)) {
				return false;
			}
		}

		return true;
	}

	#endregion

	#region Helperfunctions

	/**
	 * @param string $class
	 *
	 * @return string
	 */
	private function getTableNameFromClass(string $class): string
	{
		$parts = explode('\\', $class);
		$className = end($parts);

		return strtolower($className) . 's';
	}

	/**
	 * Prepare and bind parameters for a prepared statement.
	 *
	 * @param string $sql The SQL query with placeholders.
	 * @param array $params An associative array of parameter values.
	 * @return mysqli_stmt|false The prepared statement object or false on failure.
	 */
	private function prepareAndBindParams(string $sql, array $params): mysqli_stmt|false
	{
		// Prepare the SQL statement
		$stmt = $this->connection->prepare($sql);
		if (!$stmt) {
			Log::entry('Failed to prepare SQL statement: ' . $this->connection->error);
			return false;
		}

		if (!empty($params)) {
			$types = '';
			$bindParams = [];
			foreach ($params as $value) {
				if (is_int($value)) {
					$types .= 'i'; // Integer
				} elseif (is_float($value)) {
					$types .= 'd'; // Double (floating-point number)
				} elseif (is_string($value)) {
					$types .= 's'; // String
				} elseif ($value instanceof \DateTime) {
					$types .= 's'; // Date/time (stored as string)
					$value = $value->format('Y-m-d H:i:s'); // Format as MySQL datetime
				} else {
					$types .= 's'; // Default to string for unknown types
				}
				$bindParams[] = $value;
			}

			// Bind parameters and check for errors
			$bindResult = $stmt->bind_param($types, ...$bindParams);
			if (!$bindResult) {
				Log::entry('Failed to bind parameters: ' . $stmt->error);
				$stmt->close();
				return false;
			}
		}

		return $stmt;
	}
	#endregion

}