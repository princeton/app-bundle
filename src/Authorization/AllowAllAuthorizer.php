<?php

namespace Princeton\App\Authorization;

/**
 * AllowAllAuthorizer implements a simple RBAC scheme which
 * AUTHORIZES EVERYONE TO DO ANYTHING.
 * Use only for testing, and with great care.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class AllowAllAuthorizer implements Authorizer
{
	/**
	 * Check whether the given user is allowed to perform all of the requested actions.
	 *
	 * @param string $userid
	 * @param array $actions
	 * @return boolean
	 */
	public function checkIfAll($userid, $actions)
	{
		return true;
	}
	
	/**
	 * Check whether the given user is allowed to perform any of the requested actions.
	 *
	 * @param string $userid
	 * @param array $actions
	 * @return boolean
	 */
	public function checkIfSome($userid, $actions)
	{
		return true;
	}
	
	/**
	 * Check whether the given user has superuser privileges.
	 *
	 * @param string $userid
	 * @return boolean
	 */
	public function checkIfSuper($userid)
	{
		return true;
	}
}
