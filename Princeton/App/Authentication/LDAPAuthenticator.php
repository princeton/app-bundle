<?php

namespace Princeton\App\Authentication;

use Exception;

use Princeton\App\Traits;

/**
 * A simple implementation of LDAP authentication.
 *
 * Expects the following configuration:
 * ldap.enabled=on
 * ldap.server (full server URL - "ldaps://...")
 * ldap.realm
 * ldap.base
 * ldap.filter (optional)
 * ldap.field.userid
 * ldap.field.name
 * ldap.field.email
 * ldap.field.emplid
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class LDAPAuthenticator extends SSLOnly implements Authenticator
{
	use Traits\AppConfig;

	protected $username = false;
	protected $user = false;

	public function __construct()
	{
		parent::__construct();

		/* @var $conf \Princeton\App\Config\Configuration */
		$conf = $this->getAppConfig();

		if (!$conf->config('ldap.enabled')) {
			throw new Exception('LDAP authentication not configured!');
		}

		if (!$conf->config('ldap.server')) {
			throw new Exception('LDAP authentication is not configured properly!');
		}

		// Simple HTTP Basic authentication.
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			$this->authHeaders();
			echo 'Please login.';
			exit;
		} else {
			$ds = @ldap_connect($conf->config('ldap.server'));
			$authname = $_SERVER['PHP_AUTH_USER'];
			if (preg_match('/[^a-zA-Z0-9_.@-]/', $authname)) {
				throw new Exception('Invalid credentials!');
			}
			$query = $conf->config('ldap.field.userid') . '=' . $authname . ',' . $conf->config('ldap.base');
			if ($ds && ldap_bind($ds, $query, $_SERVER['PHP_AUTH_PW'])) {
				$search = $conf->config('ldap.field.userid') . '=' . $authname;
				if ($conf->config('ldap.filter')) {
					$search = '(&(' . $search . ')(' . $conf->config('ldap.filter') . '))';
				}
				$sr = @ldap_search($ds, $conf->config('ldap.base'), $search,
					array($conf->config('ldap.field.name'), $conf->config('ldap.field.email'), $conf->config('ldap.field.emplid')));
				if (! $sr || ldap_count_entries($ds, $sr) == 0) {
					throw new Exception('Authentication failed.');
				}
				$ldapEntry = ldap_get_attributes($ds, ldap_first_entry($ds, $sr));

				$this->username = $authname;
				$this->user = (object) array(
					'username' => $authname,
					'email' => $ldapEntry[$conf->config('ldap.field.email')][0],
					'name' => $ldapEntry[$conf->config('ldap.field.name')][0],
					'emplid' => $ldapEntry[$conf->config('ldap.field.emplid')][0]
				);
			} else {
				$this->authHeaders($conf->config('ldap.realm'));
				echo 'LDAP authentication failed.';
				exit;
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

	protected function authHeaders($realm)
	{
		header('WWW-Authenticate: Basic realm="' . $realm . '"');
		header('HTTP/1.0 401 Unauthorized');
	}
}
