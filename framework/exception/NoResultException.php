<?php
namespace bs\framework\exception;

use Exception;

class NoResultException extends Exception
{
	public function __construct($message = 'No result found', $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}