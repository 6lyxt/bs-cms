<?php

namespace framework;

use framework\test\TestRunner;

/**
 * Class Kernel
 *
 * @package bs\framework
 */
class Kernel
{
	/**
	 *
	 */
	public function __construct()
	{
		$this->init();
	}

	/**
	 * @return void
	 */
	public function init(): void
	{
		$this->runTests();
	}

	private function runTests(): void
	{
		$runner = new TestRunner();
		$runner->run();
	}
}