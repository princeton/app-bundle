<?php

namespace Princeton\App\Authentication;

use Princeton\App\Config\Configuration;

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
	protected $username = 'guest';

    protected $config;

    public function __construct(Configuration $config)
	{
		if ($config->config('guest.username')) {
			$this->username = $config->config('guest.username');
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
