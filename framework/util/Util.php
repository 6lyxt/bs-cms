<?php

namespace bs\framework\util;

use bs\framework\config\Config;
use bs\framework\exception\NoResultException;
use Exception;

class Util
{

	/**
	 * @throws \bs\framework\exception\NoResultException
	 */
	public static function throwNoResultException($message = 'No result found', $code = 0, Exception $previous = null): void
	{
		if(Config::DEBUG_MODE) {
			throw new NoResultException($message, $code, $previous);
		}
	}


}