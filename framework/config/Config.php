<?php

namespace framework\config;

use framework\util\Util;

/**
 * Class Config
 *
 * @package bs\framework\config
 */
class Config
{

	/**
	 * @throws \Exception
	 */
	public static function host()
	{
		return Util::parseConfig()['DB_HOST'];
	}

	/**
	 * @throws \Exception
	 */
	public static function user()
	{
		return Util::parseConfig()['DB_USER'];
	}

	/**
	 * @throws \Exception
	 */
	public static function pass()
	{
		return Util::parseConfig()['DB_PASS'];
	}

	/**
	 * @throws \Exception
	 */
	public static function name()
	{
		return Util::parseConfig()['DB_NAME'];
	}

	/**
	 * @return bool
	 */
	public static function debug(): bool
	{
		return true;
	}

}