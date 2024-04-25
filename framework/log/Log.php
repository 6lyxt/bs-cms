<?php

namespace bs\framework\log;

class Log
{
	/**
	 * @param string $debug
	 * @param string|bool $error
	 *
	 * @return void
	 */
	public static function entry(string $debug, string|bool $error = false): void
	{
		$debug = '[' . date('Y-m-d H:i:s') . '] ' . $debug . PHP_EOL;

		if ($error) {
			$error = date('Y-m-d H:i:s') . ' - ' . $error . PHP_EOL;
		}

		file_put_contents('log/debug.log', $debug, FILE_APPEND);

		if ($error) {
			file_put_contents('log/error.log', $error, FILE_APPEND);
		}
	}

}