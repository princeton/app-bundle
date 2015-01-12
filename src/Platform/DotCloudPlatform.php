<?php

namespace Princeton\App\Platform;

/**
 * Sets up service definitions from dotCloud environment.
 *
 * @author Kevin Perry, perry@princeton.edu
 *
 * @copyright 2014 The Trustees of Princeton University.
 */
class DotCloudPlatform extends Platform
{
	private $services;

	public function __construct()
	{
		$map = array('mysql' => 'MYSQL', 'mongo' => 'MONGODB');
		// Make it look like an AppFog services spec.
		// http://docs.dotcloud.com/guides/environment/
		foreach (array('mysql', 'mongo') as $db) {
			$env = json_decode(file_get_contents("/home/dotcloud/environment.json"), true);
			$dbPrefix = 'DOTCLOUD_DB_' . $map[$db] . '_';
			$this->services = array(
				$db => array(
					array(
						'name' => $db,
						'credentials' => array(
							'database' => $env[$dbPrefix . 'LOGIN'],
							'hostname' => $env[$dbPrefix . 'HOST'],
							'port' => $env[$dbPrefix . 'PORT'],
							'username' => $env[$dbPrefix . 'LOGIN'],
							'password' => $env[$dbPrefix . 'PASSWORD'],
						),
					),
				)
			);
		}
	}
}
