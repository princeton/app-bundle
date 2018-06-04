<?php

namespace Princeton\App\Authentication;

use phpCAS;
use Princeton\App\Config\Configuration;

/**
 * A simple implementation of CAS authentication.
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
    protected $user = false;

    protected static $prepared = false;

    protected $config;

    public function __construct(Configuration $config)
	{
        $this->config = $config;
    }

    public function prepare()
    {
        if (!self::$prepared) {
            self::$prepared = true;

            if (!$this->config->config('cas.enabled')) {
                throw new AuthenticationException('CAS not configured!');
            }

            if (!$this->config->config('cas.server')) {
                throw new AuthenticationException('CAS is not configured properly!');
            }

            phpCAS::client(
                $this->config->config('cas.SAML.enabled') ? SAML_VERSION_1_1 : CAS_VERSION_2_0,
                $this->config->config('cas.server'),
                (integer)$this->config->config('cas.port'),
                $this->config->config('cas.url')
            );

            $certfile = $this->config->config('cas.cacertfile');

            if (empty($certfile)) {
                phpCAS::setNoCasServerValidation();
            } else {
                $certfile = APPLICATION_PATH . DIRECTORY_SEPARATOR . $certfile;
                phpCAS::setCasServerCACert($certfile);
            }

            // restrict logout requests to only come from the CAS server.
            phpCAS::handleLogoutRequests();
        }
    }

    public function isAuthenticated()
    {
        if (empty($this->user)) {
            $this->prepare();

            return ($this->config->config('cas.guestAccess.allow') || phpCAS::isAuthenticated());
        } else {
            return true;
        }
    }

    public function authenticate()
    {
        if (empty($this->user)) {
            $this->prepare();

            if ($this->config->config('cas.guestAccess.allow')) {
                $username = $this->config->config('cas.guestAccess.username');

                if (empty($username)) {
                    $username = 'guest';
                }

                $this->user = (object) ['username' => $username];
            } else {
                phpCAS::forceAuthentication();
            }

            if (phpCAS::isAuthenticated()) {
                if ($this->config->config('cas.SAML.enabled')) {
                    // Attempt to get user's attributes from CAS - only works if using SAML.
                    $this->user = phpCAS::getAttributes();
                } else {
                    $this->user = [];
                }

                $this->user['username'] = phpCAS::getUser();
                $this->user = (object) $this->user;
            }
        }

        return $this->user;
    }

    public function logoff($service)
    {
        phpCAS::logoutWithRedirectService($service);
    }
}
