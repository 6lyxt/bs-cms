<?php

namespace bs\framework;

/**
 * Class Kernel
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
		$this->loadConfig();
		$this->loadHelpers();
		$this->loadDB();
	}

	/**
	 * @return void
	 */
	public function loadConfig(): void
	{
		require_once 'config/Config.php';
	}

	/**
	 * @return void
	 */
	public function loadHelpers(): void
	{
		require_once 'util/utils.php';
	}

	private function loadDB(): void
	{
		require_once 'database/DB.php';
	}
}