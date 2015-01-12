<?php

namespace Princeton\App\Platform;

/**
 * Sets up service definitions from Red Hat OpenShift environment.
 *
 * @author Kevin Perry, perry@princeton.edu
 *
 * @copyright 2014 The Trustees of Princeton University.
 */
class OpenShiftPlatform extends Platform
{
	private $services;

	public function __construct()
	{
		$map = array('mysql' => 'MYSQL', 'mongo' => 'MONGODB');
		// Make it look like an AppFog services spec.
		// https://access.redhat.com/site/documentation/en-US/OpenShift_Online/2.0/html-single/Cartridge_Specification_Guide/index.html
		foreach (array('mysql', 'mongo') as $db) {
			$dbPrefix = 'OPEN_SHIFT_' . $map[$db] . '_DB_';
			$this->services = array(
				$db => array(
					array(
						'name' => $db,
						'credentials' => array(
							'database' => getenv($dbPrefix . 'USERNAME'),
							'hostname' => getenv($dbPrefix . 'HOST'),
							'port' => getenv($dbPrefix . 'PORT'),
							'username' => getenv($dbPrefix . 'USERNAME'),
							'password' => getenv($dbPrefix . 'PASSWORD'),
						),
					),
				)
			);
		}
	}
}
