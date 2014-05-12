<?php

namespace Princeton\Authorization;

/**
 * MongoAuthorizer implements a simple RBAC scheme in MongoDB.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class MongoAuthorizer implements Authorizer
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
		if ($this->checkIfSuper($userid)) {
			return true;
		} else {
			// TODO implement checkIfAll()
			return false;
		}
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
		if ($this->checkIfSuper($userid)) {
			return true;
		} else {
			// TODO implement checkIfSome()
			return false;
		}
	}
	
	/**
	 * Check whether the given user has superuser privileges.
	 *
	 * @param string $userid
	 * @return boolean
	 */
	public function checkIfSuper($userid)
	{
		// TODO implement checkIfSuper()
		return ($userid === '-timeline-app-administrator-' || $userid === '010004094');
	}
}
