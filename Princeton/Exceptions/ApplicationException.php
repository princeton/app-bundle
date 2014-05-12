<?php

namespace Princeton\Exceptions;

use Exception;

class ApplicationException extends Exception
{
	protected $message = 'Application exception';

	public function __construct($message = null, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

