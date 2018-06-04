<?php

namespace Princeton\App\Authentication;

use Princeton\App\Config\Configuration;

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
	protected $user = false;

    protected $config;

    public function __construct(Configuration $config)
	{
        $this->config = $config;
    }

	public function isAuthenticated()
	{
	    return (!empty($this->user) || (isset($_SERVER['PHP_AUTH_USER']) && $this->authenticate()));
    }

	public function authenticate()
	{
		if (empty($this->user)) {
			if (!$this->config->config('ldap.enabled')) {
				throw new AuthenticationException('LDAP authentication not configured!');
			}
	
			if (!$this->config->config('ldap.server')) {
				throw new AuthenticationException('LDAP authentication is not configured properly!');
			}
	
			// Simple HTTP Basic authentication.
			if (!isset($_SERVER['PHP_AUTH_USER'])) {
				$this->requestCredentials($this->config->config('ldap.realm'), 'Please login');
			} else {
				$ds = @ldap_connect($this->config->config('ldap.server'));
				$authname = $_SERVER['PHP_AUTH_USER'];
				if (preg_match('/^[a-zA-Z0-9_.@-]+$/', $authname)) {
					throw new AuthenticationException('Invalid credentials!');
				}
				$query = $this->config->config('ldap.field.userid')
					. '='
					. $authname . ',' . $this->config->config('ldap.base');
				
				if ($ds && ldap_bind($ds, $query, $_SERVER['PHP_AUTH_PW'])) {
					$search = $this->config->config('ldap.field.userid') . '=' . $authname;
					if ($this->config->config('ldap.filter')) {
						$search = '(&(' . $search . ')(' . $this->config->config('ldap.filter') . '))';
					}
					
					$base = $this->config->config('ldap.base');
					$nameField = $this->config->config('ldap.field.name');
					$emailField = $this->config->config('ldap.field.email');
					$emplidField = $this->config->config('ldap.field.emplid');
					
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
					$this->requestCredentials($this->config->config('ldap.realm'), 'LDAP authentication failed.');
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
