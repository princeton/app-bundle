<?php

namespace Princeton\Config;

/**
 * Defines the interface for a simple configuration object
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class NullConfiguration implements Configuration
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
