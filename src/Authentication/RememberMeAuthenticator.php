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
		/*
		 * Device will need to remember:
		 * {user:'perry',index:0,device:'My Nexus 10',code:'00deadbeef0000deadbeef00'}
		 * - device name is for ease of user management; index is into device list.
		 * Database also holds this info; 'code' is an array - may have 2 values...
		 * {user:'perry',devices:[{name:'My Nexus 10',code:['00deadbeef0000deadbeef00']}]}
		 * When a value gets old (12hr?), push a 2nd one onto array (atomic findAndUpdate();
		 * 10 mins later, pop the old one.  This is in case there are multiple parallel reqs. from client.
		 * Both codes are valid during the interim.  If we recv. a req. with an invalid code, then invalidate all
		 * codes for that device and force re-authentication (via CAS). Need a UI page to choose "Remember me",
		 * and one to manage (i.e. delete) configured devices.
		 */
		throw new AuthenticationException('RememberMe authentication not implemented!');
	}
}
