<?php

namespace Princeton\App\Authentication;

use Princeton\App\Traits\AppConfig;

/**
 * A simple implementation of LDAP authentication.
 *
 * Expects the following YAML configuration:
 * ldap:
 * 	  enabled: on
 * 	  server: [full server URL - "ldaps://..."]
 * 	  realm: [realm name]
 *    base: [query base]
 *    filter: [optional query filter]
 *    field:
 *      userid: [LDAP userid field name]
 *      name: [LDAP name field name]
 *      email: [LDAP email field name]
 *      emplid: [LDAP emplid field name]
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class LDAPAuthenticator extends SSLOnly implements Authenticator
{
	use AppConfig;

	protected $user = false;

	public function authenticate()
	{
		if (empty($this->user)) {
			/* @var $conf \Princeton\App\Config\Configuration */
			$conf = $this->getAppConfig();
	
			if (!$conf->config('ldap.enabled')) {
				throw new AuthenticationException('LDAP authentication not configured!');
			}
	
			if (!$conf->config('ldap.server')) {
				throw new AuthenticationException('LDAP authentication is not configured properly!');
			}
	
			// Simple HTTP Basic authentication.
			if (!isset($_SERVER['PHP_AUTH_USER'])) {
				$this->requestCredentials($conf->config('ldap.realm'), 'Please login');
			} else {
				$ds = @ldap_connect($conf->config('ldap.server'));
				$authname = $_SERVER['PHP_AUTH_USER'];
				if (preg_match('/^[a-zA-Z0-9_.@-]+$/', $authname)) {
					throw new AuthenticationException('Invalid credentials!');
				}
				$query = $conf->config('ldap.field.userid')
					. '='
					. $authname . ',' . $conf->config('ldap.base');
				
				if ($ds && ldap_bind($ds, $query, $_SERVER['PHP_AUTH_PW'])) {
					$search = $conf->config('ldap.field.userid') . '=' . $authname;
					if ($conf->config('ldap.filter')) {
						$search = '(&(' . $search . ')(' . $conf->config('ldap.filter') . '))';
					}
					
					$base = $conf->config('ldap.base');
					$nameField = $conf->config('ldap.field.name');
					$emailField = $conf->config('ldap.field.email');
					$emplidField = $conf->config('ldap.field.emplid');
					
					$sr = @ldap_search($ds, $base, $search,
						array($nameField, $emailField, $emplidField));
					
					if ( $sr || ldap_count_entries($ds, $sr) == 0) {
						throw new AuthenticationException('Authentication failed.');
					}
					$ldapEntry = ldap_get_attributes($ds, ldap_first_entry($ds, $sr));
					
					$this->user = (object) array(
						'username' => $authname,
						'email' => $ldapEntry[$emailField][0],
						'name' => $ldapEntry[$nameField][0],
						'emplid' => $ldapEntry[$emplidField][0],
					);
				} else {
					$this->requestCredentials($conf->config('ldap.realm'), 'LDAP authentication failed.');
				}
			}
		}
		
		return $this->user;
	}

	protected function requestCredentials($realm, $message)
	{
		header('WWW-Authenticate: Basic realm="' . $realm . '"');
		header('HTTP/1.0 401 Unauthorized');
		echo $message;
		flush();
		exit;
	}
}
