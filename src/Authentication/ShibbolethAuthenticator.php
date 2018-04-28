<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2018 The Trustees of Princeton University.
 */

namespace Princeton\App\Authentication;

use SimpleSAML_Configuration;
use SimpleSAML\Auth\Simple;
use Princeton\App\Traits\AppConfig;

/**
 * A simple implementation of Shibboleth authentication.
 *
 * Expects the following configuration:
 * shib.enabled=on
 * shib.sp
 * shib.idp
 * shib.configDir (optional)
 * shib.guestAccess.allow (optional, default: false)
 * shib.guestAccess.username (optional, default: guest)
 */
class ShibbolethAuthenticator extends SSLOnly implements Authenticator
{
    use AppConfig;

    protected $user = false;

    protected $sp = null;

    protected static $prepared = false;

    public function prepare()
    {
        if (!self::$prepared) {
            self::$prepared = true;

            /* @var $conf \Princeton\App\Config\Configuration */
            $conf = $this->getAppConfig();

            if (!$conf->config('shib.enabled')) {
                throw new AuthenticationException('Shibboleth not configured!');
            }

            if ($conf->config('shib.configDir')) {
                $configDir = $this->getConfigDir($conf->config('shib.configDir'));
                SimpleSAML_Configuration::setConfigDir($configDir);
            }

            $this->sp = new Simple($conf->config('shib.sp') || 'default-sp');
        }
    }

    public function isAuthenticated()
    {
        if (empty($this->user)) {
            /* @var $conf \Princeton\App\Config\Configuration */
            $conf = $this->getAppConfig();

            $this->prepare();

            return ($conf->config('shib.guestAccess.allow') || $this->sp->isAuthenticated());
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

            if ($conf->config('shib.guestAccess.allow')) {
                $username = $conf->config('shib.guestAccess.username');
                if (empty($username)) {
                    $username = 'guest';
                }
                $this->user = new \stdClass();
                $this->user->username = $username;
            } else {
                $idp = $conf->config('shib.idp');
                $this->sp->requireAuth($idp ? ['saml:idp' => $idp] : null);
            }

            if ($this->sp->isAuthenticated()) {
                $this->user = (object) $this->sp->getAttributes();
            }
        }

        return $this->user;
    }

    public function logoff()
    {
        if ($this->sp) {
            $this->sp->logout();
        }
    }

    /**
     * Subclasses may override this to provide custom filesystem mapping.
     *
     * @param string $configDir
     * @return string
     */
    protected function getConfigDir($configDir)
    {
        return $configDir;
    }
}
