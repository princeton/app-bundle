<?php

namespace Princeton\Traits;

/**
 * AppConfig uses DependencyManager to supply a Configuration object to its class.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
trait AppConfig
{
	/**
	 * Get a Configuration object.
	 *
	 * @return \Princeton\Config\Configuration
	 */
	public function getAppConfig()
	{
		return \Princeton\Injection\DependencyManager::get('appConfig');
	}
}
