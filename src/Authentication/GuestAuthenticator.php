<?php

namespace Princeton\App\Authentication;

use Princeton\App\Traits;

/**
 * A simple authenticator that assumes everyone is a guest user.
 *
 * Expects the following configuration:
 * guest.username (optional, defaults to 'guest')
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class GuestAuthenticator implements Authenticator
{
	use Traits\AppConfig;

	protected $username = 'guest';

	public function __construct()
	{
		/* @var $conf \Princeton\App\Config\Configuration */
		$conf = $this->getAppConfig();

		if ($conf->config('guest.username')) {
			$this->username = $conf->config('guest.username');
		}
	}
	
	public function isAuthenticated()
	{
		return true;
	}
	
	public function authenticate()
	{
		return (object) array('username' => $this->username);
	}
}
