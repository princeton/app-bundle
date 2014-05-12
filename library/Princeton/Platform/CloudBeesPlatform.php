<?php

namespace Princeton\Platform;

/**
 * Sets up service definitions from CloudBees environment.
 *
 * @author Kevin Perry, perry@princeton.edu
 *
 * @copyright 2014 The Trustees of Princeton University.
 */
class CloudBeesPlatform extends Platform
{
	private $services;

	public function __construct()
	{
		$map = array('mysql' => 'MYSQL', 'mongo' => 'MONGODB');
		// Make it look like an AppFog services spec.
		// http://wiki.cloudbees.com/bin/view/RUN/PHP
		foreach (array('mysql', 'mongo') as $db) {
			$dbPrefix = $map[$db] . '_';
			$this->services = array(
				$db => array(
					array(
						'name' => $db,
						'credentials' => array(
							'database' => getenv($dbPrefix . 'DB_BINDING'),
							'hostname' => getenv($dbPrefix . 'HOST_BINDING'),
							'port' => getenv($dbPrefix . 'PORT_BINDING'),
							'username' => getenv($dbPrefix . 'USERNAME_BINDING'),
							'password' => getenv($dbPrefix . 'PASSWORD_BINDING'),
						),
					),
				)
			);
		}
	}
}
