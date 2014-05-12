<?php

namespace Princeton\App\Platform;

/**
 * Defines a platform environment.
 * 
 * Subclasses should set $services appropriately.
 *
 * @author Kevin Perry, perry@princeton.edu
 *
 * @copyright 2014 The Trustees of Princeton University.
 */
class Platform
{
	protected $services;
	
	public function getServices()
	{
		return $this->services;
	}
}
