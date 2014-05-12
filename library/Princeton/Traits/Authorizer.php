<?php

namespace Princeton\Traits;

/**
 * Authorizer uses DependencyManager to supply an Authorizer object to its class.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
trait Authorizer
{
	/**
	 * Get an Authorizer object.
	 * 
	 * @return \Princeton\Authorizer
	 */
	public function getAuthorizer()
	{
		return \Princeton\Injection\DependencyManager::get('authorizer');
	}
}
