<?php

namespace Princeton\App\Platform;

/**
 * Sets up service definitions from Red Hat OpenShift environment.
 *
 * @author Kevin Perry, perry@princeton.edu
 *
 * @copyright 2014 The Trustees of Princeton University.
 */
class PrincetonPlatform extends Platform
{
	protected $services = array();

	public function __construct()
	{
		// Make it look like an AppFog services spec.
		// https://access.redhat.com/site/documentation/en-US/OpenShift_Online/2.0/html-single/Cartridge_Specification_Guide/index.html
		foreach (array('mysql', 'mongo', 'neo4j') as $db) {
			$dbPrefix = 'PRIN_APP_' . strtoupper($db) . '_DB_';
			if (getenv($dbPrefix . 'ENABLED') === 'yes') {
				$this->services[$db] = array(
					array(
						'name' => $db,
						'credentials' => array(
							'dbname' => getenv($dbPrefix . 'DBNAME'),
							'database' => getenv($dbPrefix . 'DBNAME'),
							'host' => getenv($dbPrefix . 'HOST'),
							'hostname' => getenv($dbPrefix . 'HOST'),
							'port' => getenv($dbPrefix . 'PORT'),
							'user' => getenv($dbPrefix . 'USERNAME'),
							'username' => getenv($dbPrefix . 'USERNAME'),
							'password' => getenv($dbPrefix . 'PASSWORD'),
						),
					),
				);
			}
		}
	}
}
