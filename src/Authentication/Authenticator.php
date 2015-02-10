<?php

namespace Princeton\App\Authentication;

/**
 * A simple authentication interface.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
interface Authenticator
{
	/**
	 * The authenticate() method should attempt to authenticate the user.
	 * If successful, it should return an application-specific object which
	 * identifies the authenticated user. If not successful, it sohuld
	 * throw an AuthenticationException.
	 *
	 * @return mixed A user object (application-specific)
	 */
	public function authenticate();
}
