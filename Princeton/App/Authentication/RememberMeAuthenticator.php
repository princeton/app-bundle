<?php

namespace Princeton\App\Authentication;

use Princeton\App\Traits\AppConfig;

/**
 * A simple authenticator that assumes everyone is a guest user.
 *
 * Expects the following configuration:
 * guest.username (optional, defaults to 'guest')
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class RememberMeAuthenticator implements Authenticator
{
	use AppConfig;

	protected $username = 'guest';
	protected $user;

	public function __construct()
	{
		/* @var $conf \Princeton\App\Config\Configuration */
		$conf = $this->getAppConfig();

		if ($conf->config('auth.rememberme.allow')) {
			$this->username = $this->decodeCookie();
			$this->user = (object) array('username' => $this->username);
		} else {
			throw new AuthenticationException('RememberMe authentication not allowed!');
		}
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getUser()
	{
		return $this->user;
	}
	
	private function decodeCookie()
	{
		// TODO Implement RememberMeAuthenticator.
		throw new AuthenticationException('RememberMe authentication not implemented!');
	}
}
