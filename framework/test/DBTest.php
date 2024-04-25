<?php

namespace framework\test;

use framework\database\Record\Record;

class DBTest implements ITest
{
	/**
	 * @return \framework\database\Record\Record
	 */
	public function runTest(): Record
	{
		$test = DBTestRecord::init();
		$test->name = 'John Doe';
		$test->age = 25;
		$test->save();

		return $test;
	}

	/**
	 * @return string
	 */
	public function getTestName(): string
	{
		return "Database Test";
	}

	/**
	 * @return string
	 */
	public function getTestResult(): string
	{
		$results = $this->runTest()->getReadable();

		if ($results['name'] == 'John Doe' && $results['age'] == 25) {
			return "Test Passed";
		} else {
			return "Test Failed";
		}
	}
}