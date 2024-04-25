<?php

namespace framework\test;

use framework\database\Record\Record;

class DBTestRecord extends Record
{
	private static string $table = 'db_test_table';

	private static array $columns = [
		'id'         => 'int',
		'name'       => 'string',
		'age'        => 'int',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];



}