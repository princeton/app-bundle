<?php

namespace Princeton\App\Traits;

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
	 * @return \Princeton\App\Authorizer
	 */
	public function getAuthorizer()
	{
		return \Princeton\App\Injection\DependencyManager::get('authorizer');
	}
}
