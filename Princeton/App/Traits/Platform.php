<?php

namespace Princeton\App\Traits;

/**
 * Platform uses DependencyManager to supply a Platform object to its class.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
trait Platform
{
	/**
	 * Get a Platform object.
	 * 
	 * @return \Princeton\App\Platform\Platform
	 */
	public function getPlatform()
	{
		return \Princeton\App\Injection\DependencyManager::get('platform');
	}
}
