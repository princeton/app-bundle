<?php

namespace Princeton\App\Traits;

/**
 * Strings uses DependencyManager to supply a Strings object to its class.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
trait Strings
{
	/**
	 * Get a Strings object.
	 * 
	 * @return \Princeton\App\Strings\Strings
	 */
	public function getStrings()
	{
		return \Princeton\App\Injection\DependencyManager::get('strings');
	}
}
