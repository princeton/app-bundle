<?php

namespace Princeton\Traits;

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
	 * @return \Princeton\Strings\Strings
	 */
	public function getStrings()
	{
		return \Princeton\Injection\DependencyManager::get('strings');
	}
}
