<?php

namespace Princeton\App\Platform;

use Princeton\App\Injection\Injectable;

/**
 * Defines a platform environment.
 * 
 * Subclasses should set $services appropriately.
 *
 * @author Kevin Perry, perry@princeton.edu
 *
 * @copyright 2014 The Trustees of Princeton University.
 */
class Platform implements Injectable
{
	protected $services;
	
	public function getServices()
	{
		return $this->services;
	}
}
