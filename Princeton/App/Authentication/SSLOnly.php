<?php

namespace Princeton\App\Authentication;

/**
 * Should be a trait!
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class SSLOnly
{
	public function __construct()
	{
		// Make sure connection is HTTPS.
		if (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS']) {
			header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
			exit();
		}
	}
}
