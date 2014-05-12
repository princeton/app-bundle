<?php

namespace Princeton\App\Authorization;

/**
 * Authorizer defines a simple authorization interface suitable for RBAC implementations.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
interface Authorizer
{
	/**
	 * Check whether the given user is allowed to perform all of the requested actions.
	 *
	 * @param string $userid
	 * @param array $actions
	 * @return boolean
	 */
	public function checkIfAll($userid, $actions);
	
	/**
	 * Check whether the given user is allowed to perform any of the requested actions.
	 *
	 * @param string $userid
	 * @param array $actions
	 * @return boolean
	 */
	public function checkIfSome($userid, $actions);
	
	/**
	 * Check whether the given user has superuser privileges.
	 *
	 * @param string $userid
	 * @return boolean
	 */
	public function checkIfSuper($userid);
}
