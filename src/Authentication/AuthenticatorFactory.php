<?php

namespace Princeton\App\Authentication;

use Princeton\App\Authentication\Authenticator;

/**
 * A simple authenticator factory interface.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2018 The Trustees of Princeton University.
 */
interface AuthenticatorFactory
{
	public function getAuthenticator(string $classname): Authenticator;
}
