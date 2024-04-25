<?php

namespace framework\test;

/**
 * Interface TestInterface
 */
interface ITest
{

	/**
	 * Perform a test.
	 *
	 * @return mixed
	 */
	public function runTest(): mixed;

	/**
	 * Get the name of the test.
	 *
	 * @return string
	 */
	public function getTestName(): string;

	/**
	 * Get the result of the test.
	 *
	 * @return mixed
	 */
	public function getTestResult(): mixed;
}