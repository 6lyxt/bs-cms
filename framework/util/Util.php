<?php

namespace framework\util;

use framework\config\Config;
use framework\exception\NoResultException;
use Exception;

class Util
{

	/**
	 * @throws \framework\exception\NoResultException
	 */
	public static function throwNoResultException($message = 'No result found', $code = 0, Exception $previous = null): void
	{
		if(Config::debug()) {
			throw new NoResultException($message, $code, $previous);
		}
	}

	/**
	 * @throws \Exception
	 */
	public static function parseConfig(): array
	{
		$file = file_get_contents('config.json');

		if(!$file) {
			throw new Exception('Could not read config file');
		}

		return json_decode($file, true);
	}


}