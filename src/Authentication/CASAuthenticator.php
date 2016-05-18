<?php

namespace Princeton\App\Authentication;

use phpCAS;

use Princeton\App\Traits\AppConfig;

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
    use AppConfig;
    
    protected $user = false;

    protected static $prepared = false;

    public function prepare()
    {
        if (!self::$prepared) {
            self::$prepared = true;

            /* @var $conf \Princeton\App\Config\Configuration */
            $conf = $this->getAppConfig();

            if (!$conf->config('cas.enabled')) {
                throw new AuthenticationException('CAS not configured!');
            }

            if (!$conf->config('cas.server')) {
                throw new AuthenticationException('CAS is not configured properly!');
            }

            phpCAS::client(
                $conf->config('cas.SAML.enabled') ? SAML_VERSION_1_1 : CAS_VERSION_2_0,
                $conf->config('cas.server'),
                (integer)$conf->config('cas.port'),
                $conf->config('cas.url')
            );

            $certfile = $conf->config('cas.cacertfile');

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
            /* @var $conf \Princeton\App\Config\Configuration */
            $conf = $this->getAppConfig();

            $this->prepare();

            return ($conf->config('cas.guestAccess.allow') || phpCAS::isAuthenticated());
        } else {
            return true;
        }
    }
    
    public function authenticate()
    {
        if (empty($this->user)) {
            /* @var $conf \Princeton\App\Config\Configuration */
            $conf = $this->getAppConfig();

            $this->prepare();

            if ($conf->config('cas.guestAccess.allow')) {
                $username = $conf->config('cas.guestAccess.username');
                if (empty($username)) {
                    $username = 'guest';
                }
                $this->user = new \stdClass();
                $this->user->username = $username;
            } else {
                phpCAS::forceAuthentication();
            }
    
            if (phpCAS::isAuthenticated()) {
                if ($conf->config('cas.SAML.enabled')) {
                    // Attempt to get user's attributes from CAS - only works if using SAML.
                    $this->user = (object) phpCAS::getAttributes();
                } else {
                    $this->user = new \stdClass();
                }
                $this->user->username = phpCAS::getUser();
            }
        }
        
        return $this->user;
    }

    public function logoff($service)
    {
        phpCAS::logoutWithRedirectService($service);
    }
}
