<?php

namespace Princeton\Config;

/**
 * Defines the interface for a simple configuration object
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
interface Configuration
{
	public function config($key);
	public function clearCached();
}
