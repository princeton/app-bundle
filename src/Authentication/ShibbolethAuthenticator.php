<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2018 The Trustees of Princeton University.
 */

namespace Princeton\App\Authentication;

use SimpleSAML_Configuration;
use SimpleSAML\Auth\Simple;
use Princeton\App\Config\Configuration;

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
    protected $user = false;

    protected $sp = null;

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

            if (!$this->config->config('shib.enabled')) {
                throw new AuthenticationException('Shibboleth not configured!');
            }

            if ($this->config->config('shib.configDir')) {
                $configDir = $this->getConfigDir($this->config->config('shib.configDir'));
                SimpleSAML_Configuration::setConfigDir($configDir);
            }

            $this->sp = new Simple($this->config->config('shib.sp') || 'default-sp');
        }
    }

    public function isAuthenticated()
    {
        if (empty($this->user)) {
            $this->prepare();

            return ($this->config->config('shib.guestAccess.allow') || $this->sp->isAuthenticated());
        } else {
            return true;
        }
    }

    public function authenticate()
    {
        if (empty($this->user)) {
            $this->prepare();

            if ($this->config->config('shib.guestAccess.allow')) {
                $username = $this->config->config('shib.guestAccess.username');
                if (empty($username)) {
                    $username = 'guest';
                }
                $this->user = new \stdClass();
                $this->user->username = $username;
            } else {
                $idp = $this->config->config('shib.idp');
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
