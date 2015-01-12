<?php

namespace Princeton\App\Platform;

/**
 * Sets up service definitions from AppFog environment.
 *
 * @author Kevin Perry, perry@princeton.edu
 *
 * @copyright 2014 The Trustees of Princeton University.
 */
class AppFogPlatform extends Platform
{
	protected $services;

	public function __construct()
	{
		// https://docs.appfog.com/services
		$serviceTypes = json_decode(getenv('VCAP_SERVICES'), true);
		foreach ( $serviceTypes as $type => &$services ) {
			foreach ( $services as &$service ) {
				$service['credentials']['database'] = $service['credentials']['name'];
			}
		}
		$this->services = $services;
	}
}
