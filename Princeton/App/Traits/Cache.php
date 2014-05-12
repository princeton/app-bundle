<?php

namespace Princeton\App\Traits;

/**
 * Cache uses DependencyManager to supply a Cache object to its class.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
trait Cache
{
	/**
	 * Get a Cache object.
	 * 
	 * @return \Doctrine\Common\Cache\Cache
	 */
	public function getCache()
	{
		return \Princeton\App\Injection\DependencyManager::get('cache');
	}
}
