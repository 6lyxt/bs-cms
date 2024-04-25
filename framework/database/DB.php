<?php

namespace bs\framework\database;

use bs\framework\config\Config;
use bs\framework\log\Log;
use mysqli;
use mysqli_result;

/**
 * Class DB
 *
 * @package bs\framework\database
 *
 */
class DB
{

	/**
	 * @return \mysqli
	 */
	private function connect(): mysqli
	{
		$host = Config::DB_HOST;
		$user = Config::DB_USER;
		$pass = Config::DB_PASS;
		$db = Config::DB_NAME;

		$conn = new mysqli($host, $user, $pass, $db);

		if ($conn->connect_error) {
			Log::entry('Connected successfully', $conn->connect_error);
		}

		Log::entry('Connected successfully');

		return $conn;
	}

	/**
	 * @param string $table
	 *
	 * @return \mysqli_result|bool
	 */
	public static function all(string $table): mysqli_result|bool
	{
		$inst = new self();
		$connection = $inst->connect();

		return $connection->query("SELECT * FROM $table");
	}

	/**
	 * @param string $table
	 * @param string $column
	 * @param string $value
	 *
	 * @return \mysqli_result|bool
	 */
	public static function filter(string $table, string $column, string $value): mysqli_result|bool
	{
		$inst = new self();
		$connection = $inst->connect();

		return $connection->query("SELECT * FROM $table WHERE $column = '$value'");
	}

	/**
	 * @param string $table
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function insert(string $table, array $data): bool
	{
		$inst = new self();
		$connection = $inst->connect();

		$columns = implode(', ', array_keys($data));
		$values = implode("', '", array_values($data));

		return $connection->query("INSERT INTO $table ($columns) VALUES ('$values')");
	}

	/**
	 * @param string $table
	 * @param string $column
	 *
	 * @return \mysqli_result|bool
	 */
	public static function softDelete(string $table, string $column): mysqli_result|bool
	{
		// should not delete the column, only clear the value
		$inst = new self();
		$connection = $inst->connect();

		return $connection->query("UPDATE $table SET $column = NULL");
	}

	/**
	 * @param string $table
	 * @param string $column
	 * @param string $value
	 *
	 * @return bool
	 * @deprecated use softDelete instead
	 */
	public static function delete(string $table, string $column, string $value): bool
	{
		$inst = new self();
		$connection = $inst->connect();

		return $connection->query("DELETE FROM $table WHERE $column = '$value'");
	}

	/**
	 * @param string $table
	 * @param array $entries
	 *
	 * @return bool
	 */
	public static function buildOneToManyRelation(string $table, array $entries): bool
	{
		$inst = new self();
		$connection = $inst->connect();

		foreach ($entries as $column => $entry) {
			$relation = "ALTER TABLE $table ADD FOREIGN KEY ($column) REFERENCES $entry($column)";
			$connection->query($relation);

			return true;
		}

		return false;
	}

	/**
	 * @param string $table
	 * @param array $entries
	 * @unused this method is not implemented yet
	 *
	 * @return bool
	 */
	public static function buildManyToManyRelation(string $table, array $entries): bool
	{
		return false;
	}

	/**
	 * @param string $table
	 * @param array $entries
	 *
	 * @return bool
	 */
	public static function buildOneToOneRelation(string $table, array $entries): bool
	{
		$inst = new self();
		$connection = $inst->connect();

		foreach ($entries as $column => $entry) {
			$relation = "ALTER TABLE $table ADD FOREIGN KEY ($column) REFERENCES $entry($column)";
			$connection->query($relation);

			return true;
		}

		return false;
	}

	/**
	 * @param string $type
	 * @param string $table
	 * @param string $column
	 *
	 * @return \mysqli_result|bool
	 */
	public static function build_relation(string $type, string $table, string $column): mysqli_result|bool
	{
		$inst = new self();
		$connection = $inst->connect();

		$relation = match ($type) {
			RelationsEnum::OneToOne => self::buildOneToOneRelation($table, $column, $type),
			RelationsEnum::OneToMany => self::buildOneToManyRelation($table, $column, $type),
			RelationsEnum::ManyToMany => self::buildManyToManyRelation($table, $column, $type),
		};

		return $connection->query($relation);
	}

	/**
	 * @param string $table
	 * @param string $column
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function addColumn(string $table, string $column, string $type): bool
	{
		$inst = new self();
		$connection = $inst->connect();

		return $connection->query("ALTER TABLE $table ADD $column $type");
	}

	/**
	 * @param string $table
	 * @param array $data
	 *
	 * @return \mysqli_result|bool
	 */
	public static function update(string $table, array $data): mysqli_result|bool
	{
		$inst = new self();
		$connection = $inst->connect();

		$columns = implode(', ', array_keys($data));
		$values = implode("', '", array_values($data));

		return $connection->query("UPDATE $table SET $columns = '$values'");
	}
}