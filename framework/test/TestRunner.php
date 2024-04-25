<?php

namespace framework\test;

/**
 * Class TestRunner
 */
class TestRunner
{

	/**
	 * @return void
	 */
	public function run(): void
	{
		$testClasses = $this->getTestClasses();
		$this->runTests($testClasses);
	}

	/**
	 * @return array
	 */
	private function getTestClasses(): array
	{
		$testClasses = [];
		$files = scandir(__DIR__);
		foreach ($files as $file) {
			if (strpos($file, 'Test.php') !== false) {
				$testClasses[] = $file;
			}
		}

		return $testClasses;
	}

	/**
	 * @param $testClasses
	 *
	 * @return void
	 */
	private function runTests($testClasses): void
	{
		foreach ($testClasses as $testClass) {
			$testClass = str_replace('.php', '', $testClass);
			$testClass = 'framework\\test\\' . $testClass;
			$test = new $testClass();
			echo $test->getTestName() . ': ' . $test->getTestResult() . PHP_EOL;
		}
	}
}