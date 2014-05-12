<?php

namespace Princeton\Authentication;

use Exception;

class AuthenticationException extends Exception
{
	protected $message = 'Authentication exception';

	public function __construct($message = null, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

