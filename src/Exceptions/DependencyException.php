<?php

namespace Princeton\App\Exceptions;

class DependencyException extends \Exception {
	public function __construct($message = 'Dependency Exception', $code = null, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
