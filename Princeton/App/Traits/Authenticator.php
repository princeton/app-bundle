<?php

namespace Princeton\App\Traits;

/**
 * Authenticator uses DependencyManager to supply an Authenticator object to its class.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
trait Authenticator
{
	/**
	 * Get an Authenticator object.
	 * 
	 * @return \Princeton\App\Authentication\Authenticator
	 */
	public function getAuthenticator()
	{
		return \Princeton\App\Injection\DependencyManager::get('authenticator');
	}
}
