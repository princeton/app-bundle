<?php

namespace Princeton\App\Authentication;

use phpCAS;

use Princeton\App\Traits;

/**
 * A simple implementation of LDAP authentication.
 *
 * Expects the following configuration:
 * cas.enabled=on
 * cas.server
 * cas.port
 * cas.url
 * cas.guestAccess.allow (optional, default: false)
 * cas.guestAccess.username (optional, default: guest)
 * cas.cacertfile (optional for server cert validation)
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class CASAuthenticator extends SSLOnly implements Authenticator
{
	use Traits\AppConfig;

	protected $username = false;
	protected $user = false;

	public function __construct()
	{
		parent::__construct();

		/* @var $conf \Princeton\App\Config\Configuration */
		$conf = $this->getAppConfig();

		if (!$conf->config('cas.enabled')) {
			throw new AuthenticationException('CAS authentication not configured!');
		}

		if (!$conf->config('cas.server')) {
			throw new AuthenticationException('CAS authentication is not configured properly!');
		}
		//phpCAS::setDebug('/tmp/CASdebug.log');

		phpCAS::client($conf->config('cas.SAML.enabled') ? SAML_VERSION_1_1 : CAS_VERSION_2_0,
			$conf->config('cas.server'), (integer)$conf->config('cas.port'), $conf->config('cas.url'));

		$certfile = APPLICATION_PATH . DIRECTORY_SEPARATOR . $conf->config('cas.cacertfile');
		if (empty($certfile)) {
			phpCAS::setNoCasServerValidation();
		} else {
			phpCAS::setCasServerCACert($certfile);
		}
		// restrict logout requests to only come from the CAS server.
		phpCAS::handleLogoutRequests();

		if ($conf->config('cas.guestAccess.allow')) {
			$p = $conf->config('cas.guestAccess.allow');
			if ($conf->config('cas.guestAccess.username')) {
				$this->username = $conf->config('cas.guestAccess.username');
			} else {
				$this->username = 'guest';
			}
			$this->user = (object) array('username' => $this->username);
		} else {
			phpCAS::forceAuthentication();
		}

		if (phpCAS::isAuthenticated()) {
			$this->username = phpCAS::getUser();
			if ($conf->config('cas.SAML.enabled')) {
				// Attempt to get user's attributes from CAS - only works if using SAML.
				$this->user = (object) phpCAS::getAttributes();
				if (!isset($this->user->{$conf->config('cas.SAML.idAttribute')})) {
					throw new AuthenticationException('No SAML ID for user!');
				}
				$this->user->username = $this->username;
			} else {
				$this->user = (object) array('username' => $this->username);
			}
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

	public function logoff($service)
	{
		phpCAS::logoutWithRedirectService($service);
	}
}
