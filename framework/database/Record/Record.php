<?php

namespace framework\database\Record;

use framework\database\DB;
use framework\util\Util;

/**
 * Master class representing a database entry to table.
 *
 * @package bs\framework\database\Record
 */
abstract class Record
{
	private DB $connector;

	/**
	 * @var string
	 */
	private static string $table;

	/**
	 * @var array
	 */
	private static array $columns = [];

	/**
	 * @var array
	 */
	private static array $one_to_one = [];

	/**
	 * @var array
	 */
	private static array $one_to_many = [];

	/**
	 * @var array
	 */
	private static array $many_to_many = [];

	/**
	 * Record constructor.
	 */
	public function __construct()
	{
		$this->connector = new DB();

		// Automatically define properties based on $columns array
		foreach (static::getColumns() as $field => $type) {
			$this->{$field} = null; // Initialize property with null value
		}
	}

	/**
	 * Initialize the Record instance.
	 *
	 * @return static
	 */
	public static function init(): static
	{
		$inst = new static();

		// Build columns
		foreach (static::getColumns() as $field => $type) {
			$inst->connector->addColumn(static::getTable(), $field, $type);
		}

		// Build one-to-one relation
		if (isset(static::$one_to_one)) {
			$inst->connector->buildOneToOneRelation(static::getTable(), static::$one_to_one);
		}

		// Build one-to-many relation
		if (isset(static::$one_to_many)) {
			$inst->connector->buildOneToManyRelation(static::getTable(), static::$one_to_many);
		}

		return $inst;
	}

	/**
	 * Get a record by ID.
	 *
	 * @param int $id
	 *
	 * @return bool|array|null
	 * @throws \framework\exception\NoResultException
	 */
	public static function get(int $id): bool|array|null
	{
		$result = (new DB)->filter(static::$table, 'id', $id);

		if ($result) {
			return $result->fetch_object();
		}

		Util::throwNoResultException();

		return null;
	}

	/**
	 * Save the current record by updating or inserting into the database.
	 *
	 * @return bool
	 */
	public function save(): bool
	{
		$data = [];

		// Populate data with non-null properties
		foreach ($this as $key => $value) {
			if (!is_null($value) && array_key_exists($key, static::$columns)) {
				$data[$key] = $value;
			}
		}

		if (property_exists($this, 'id') && !is_null($this->id)) {
			// Update existing record
			return $this->connector->update(static::getTable(), $data);
		} else {
			// Insert new record
			return $this->connector->insert(static::getTable(), $data);
		}
	}

	/**
	 * Magic method to dynamically set properties.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set(string $name, mixed $value): void
	{
		if (array_key_exists($name, static::$columns)) {
			$this->{$name} = $value;
		}
	}

	/**
	 * @return array
	 */
	public static function getColumns(): array
	{
		return self::$columns;
	}

	public static function getTable(): string
	{
		return self::$table;
	}
}
