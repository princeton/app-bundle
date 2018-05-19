<?php

namespace Princeton\App\Config;

use Princeton\App\Injection\Injectable;

/**
 * Defines the interface for a simple configuration object
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class NullConfiguration implements Configuration, Injectable
{
	public function config($key)
	{
		return null;
	}
	
	public function clearCached()
	{
		return true;
	}
}
