<?php

namespace bs\framework\database\Record;

use bs\framework\database\DB;
use bs\framework\util\Util;

/**
 * master class of
 * represents a database entry to table
 *
 * @package bs\framework\database
 */
class Record
{
	/**
	 * @var string
	 */
	private static string $table;

	/**
	 * @var array $columns
	 *
	 * the columns array represents the available columns, which can be accessed via field names
	 */
	private static array $columns = [];

	/**
	 * @var array $one_to_many
	 * represents a one-to-one relation
	 */
	private static array $one_to_one = [];

	/**
	 * @var array $one_to_many
	 * represents a one-to-many relation
	 */
	private static array $one_to_many = [];

	/**
	 * @var array $many_to_many
	 * represents a many-to-many relation
	 */
	private static array $many_to_many = [];

	/**
	 * @return self
	 */
	public static function init(): static
	{
		$inst = new self();

		// build columns
		foreach (static::$columns as $field => $type) {
			DB::addColumn(static::$table, $field, $type);
		}

		// build one-to-many relation (has_many)
		if (isset(static::$one_to_one)) {
			DB::buildOneToOneRelation(static::$table, static::$one_to_many);
		}

		// build one-to-many relation (has_many)
		if (isset(static::$one_to_many)) {
			DB::buildOneToManyRelation(static::$table, static::$one_to_many);
		}

		return $inst;
	}

	/**
	 * @param int $id
	 *
	 * @return bool|array|null
	 * @throws \bs\framework\exception\NoResultException
	 */
	public static function get(int $id): bool|array|null
	{
		$result = DB::filter(static::$table, 'id', $id);

		if ($result) {
			return $result->fetch_object();
		}

		Util::throwNoResultException();

		return null;
	}
}