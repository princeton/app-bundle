<?php

namespace Princeton\App\Authentication;

use Princeton\App\Traits\AppConfig;

/**
 * An authenticator that uses a persistent login cookie to validate new sessions.
 *
 * Device will need to remember:
 * {user:'perry',device:'a9b8c7deadbeef4321',token:'0123456789abcdef'}
 *
 * Database also holds this info, multiple per user, perhaps something like this:
 * {user:'perry',devices:{
 *         'a9b8c7deadbeef4321': {name:'My Nexus 10',token:'fedcba987654321'},
 * }}
 *
 * The server-side 'token' value is a has of the client's token.
 * (this fact is transparent to the delegate API.)
 *
 * Application should provide a UI page to choose "Remember me",
 * and one to manage (i.e. delete) configured devices individually or "all".
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */
abstract class RememberMeAuthenticator extends SSLOnly implements Authenticator
{
    use AppConfig;

    /*
     * Name of the client cookie used to share the RememberMe data.
     */
    const COOKIE_NAME = 'rmauth_token';

    /*
     * Name of the client cookie used to share the RememberMe data.
     */
    const COOKIE_NAME_DEV = 'rmauth_device';
    
    /*
     * This is of questionable utility. Should probably NOT ever set
     * rememberme.cookiePath, so that this defaults to '/'.
     * In that case, authentication procedes linearly.
     * The URL path for which the RememberMe cookies are valid.
     * If this is set to some other value, then it is used as the
     * cookie's path parameter, and we must
     * redirect there-and-back in order to get and process the rmauth_token.
     * I will leave this ability in for now, to be considered further.
     */
    private $cookiePath;

    /*
     * Inactivity timeout on user sessions, in seconds.
     * Defaults to one hour.
     */
    private $sessionTTL;

    /*
     * How long until tokens stored in the database become invalid.
     * Defaults to one year.
     */
    private $tokenTTL;

    /*
     * Delegate for handling application API.
     * @var $delegate RememberMeDelegate
     */
    private $delegate;
    
    /*
     * Cached user object (application-specific).
     */
    private $user = false;

    /**
     * @param $delegate RememberMeDelegate
     */
    public function __construct($delegate = null)
    {
        $appConfig = $this->getAppConfig();

        /* See notes re $this->cookiePath above. */
        $cookiePath = $appConfig->config('rememberMe.cookiePath');
        $this->cookiePath = empty($cookiePath) ? '/' : $cookiePath;

        $sessionTTL = $appConfig->config('rememberMe.sessionTTL');
        $this->sessionTTL = empty($sessionTTL) ? 3600 : $sessionTTL;

        $tokenTTL = $appConfig->config('rememberMe.tokenTTL');
        $this->tokenTTL = empty($tokenTTL) ? 315360005 : $tokenTTL;

        $this->setDelegate($delegate);
    }

    /**
     * Set the application delegate.
     */
    public function setDelegate($delegate)
    {
    	// TODO default if null?
        $this->delegate = $delegate;
    }

    /**
     * Test whether we can authenticate the user.
     *
     * @see \Princeton\App\Authentication\Authenticator::authenticate()
     */
    public function authenticate()
    {
        if ($this->delegate && !$this->user) {
            session_start();
            $inSession = true;
            $now = time();
            $expired = $now - $this->sessionTTL;
            
            if (isset($_SESSION['RMAUTH_LAST']) && $_SESSION['RMAUTH_LAST'] < $expired) {
                session_unset();
                session_destroy();
                $inSession = false;
            }
            
            if (isset($_SESSION['RMAUTH_USER'])) {
                // There is an active session.
                $this->user = new \stdClass();
                $this->user->username = $_SESSION['RMAUTH_USER'];
                $_SESSION['RMAUTH_LAST'] = $now;
            } else {
                /* See notes re $this->cookiePath above. */
                if ($this->cookiePath != '/' && !$this->startsWith($_SERVER['REQUEST_URI'], $this->cookiePath)) {
                    header('Location: ' . $this->cookiePath . '?rmauth_redirect=' . urlencode($_SERVER['REQUEST_URI']));
                    exit();
                }
                /* If rememberme.cookiePath is set, then we only get here if user is logging in via $cookiePath. */
                
                $cookie = $this->clientCookie();
                if ($cookie) {
                    $cToken = $this->hash($cookie['token']);
                    $data = $this->delegate->getToken($cookie['user'], $cookie['device']);
                    list ($sToken, $sTime) = $this->decodeServerToken($data);
                    $expired = $now - $this->tokenTTL;
                    
                    if ($cToken === $sToken && $sTime > $expired) {
                        // Matching, valid token - authenticated login.
                        // Cache user's id and set up new login token.
                        $this->user = new \stdClass();
                        $this->user->username = $cookie['user'];
                        $_SESSION['RMAUTH_USER'] = $cookie['user'];
                        $_SESSION['RMAUTH_LAST'] = $now;
                        $this->setupTokens($cookie['user'], $cookie['device']);
                        // Close session now in case authenticatedHook doesn't return.
                        session_write_close();
                        $inSession = false;
                        if ($this->cookiePath != '/' && isset($_REQUEST['rmauth_redirect'])) {
                            header('Location: ' . $_REQUEST['rmauth_redirect']);
                            exit();
                        }
                    } else {
                        // Compromised cookie or expired token.
                        // Disable bad cookie and fail authentication.
                        $cookie['token'] = '-none-';
                        $this->setClientCookie($cookie);
                    }
                } else {
                    // Expire any malformed cookie and fail authentication.
                    $this->setClientCookie(null);
                }
            }
            
            if ($inSession) {
                session_write_close();
            }
        }
        
        return $this->user;
    }

    /**
     * For MultiAuthenticator.  Runs after user has been authenticated
     * by some auth module lower in the stack.
     * @param mixed $user
     */
    public function afterAuthenticated($user)
    {
        if ($this->delegate) {
            $this->configureDeviceUser($user->{'username'}, false);
            session_write_close();
            /* See notes re $this->cookiePath above. */
            if ($this->cookiePath != '/' && isset($_REQUEST['rmauth_redirect'])) {
                header('Location: ' . $_REQUEST['rmauth_redirect']);
                exit();
            }
        }
    }

    /**
     * Should only be used to configure current device for current user -
     * NOT by admin to try to configure somebody else!
     * Assumes that $username is name of user who has been properly
     * authenticated by some means other than this class.
     * @param string $username Name of the currently authenticated user.
     * @param bool $always Whether to do first-time setup if no cookies
     */
    public function configureDeviceUser($username, $always = true)
    {
        if ($this->delegate && !empty($username)) {
            $cookie = $this->clientCookie();
            
            if ($cookie) {
                if ($username != $cookie['user']) {
                    // User mis-match (so don't do anything).
                    // Shouldn't happen.
                } elseif ($cookie['token'] !== '-none-') {
                    // We can get here if we just authenticated the
                    // user ourselves (so nothing to do).
                    // Or ... what???
                } else {
                    // Compromised cookie or expired token,
                    // but user has re-authenticated.
                    $this->setupTokens($username, $cookie['device']);
                }
            } elseif (!empty($_COOKIE[self::COOKIE_NAME_DEV])) {
                // No token cookie, but there is a device cookie.
                // Re-initialize.
                $device = $_COOKIE[self::COOKIE_NAME_DEV];
                $token = $this->delegate->getToken($username, $device);
                $this->setupTokens($username, $device);
            } elseif ($always) {
                // No cookie - first time setup for this device.
                $device = $this->generateDeviceId();
                $this->setupTokens($username, $device);
            }
        }
    }
    
    protected function setupTokens($username, $device)
    {
        $token = $this->generateToken();
        $tokenData = $this->encodeServerToken($token);
        $this->delegate->setToken($username, $device, $tokenData);
        $this->setClientCookie(array(
            'user' => $username,
            'device' => $device,
            'token' => $token
        ));
    }
    
    /**
     * Generate a random device identifier.
     * @return string
     */
    private function generateDeviceId()
    {
        return $this->makeNonce();
    }
    
    /**
     * Generate a random token string.
     * @return string
     */
    private function generateToken()
    {
        return $this->makeNonce();
    }
    
    /**
     * Generate a replacement token.
     * @param array $cookie
     * @return string The new token.
     */
    private function refreshToken($cookie)
    {
        if ($cookie['token'] === '--refresh--') {
            return $cookie['token'];
        } else {
            return $this->generateToken();
        }
    }
    
    /**
     * Turn a token into its storable hash, for security.
     * @param string $token
     * @return string
     */
    private function hash($token)
    {
        return hash('sha256', $token);
    }
    
    /**
     * encode as a hash of the token in a json string with a timestamp.
     * @param string $token
     * @return string
     */
    private function encodeServerToken($token)
    {
        return json_encode(array($this->hash($token), time()));
    }
    
    /**
     * Decode into an array containing hash of token and a timestamp.
     * @param string $tokenData
     * @return array
     */
    private function decodeServerToken($tokenData)
    {
        if (strlen($tokenData) > 0) {
            $info = json_decode($tokenData);
        }
        if (!is_array($info) || count($info) != 2) {
            $info = array(null, 0);
        }
        return array_values($info);
    }
    
    /**
     * Return the client cookie, or false if it is malformed or nonexistant.
     * @return mixed
     */
    private function clientCookie()
    {
        $cookie = false;
        
        if (!empty($_COOKIE[self::COOKIE_NAME])) {
            $cookie = json_decode($_COOKIE[self::COOKIE_NAME], true);
            /* We expect the cookie to contain user, device and token. */
            if (
                !is_array($cookie)
                || empty($cookie['user'])
                || empty($cookie['device'])
                || empty($cookie['token'])
            ) {
                $cookie = false;
            }
        }
        
        return $cookie;
    }
    
    /**
     * Set the cookie in the response to the client; or forcibly expire
     * the cookie if $cookie is null.
     * @param array $cookie
     */
    private function setClientCookie($cookie)
    {
        if ($cookie) {
            $value = json_encode($cookie);
            $expires = 0;
        } else {
            $value = '';
            $expires = 1;
        }
        setcookie(self::COOKIE_NAME, $value, $expires, $this->cookiePath, null, true, true);
    }
    
    /**
     * Returns a random ~44-character base-64 string.
     * @return string
     */
    private function makeNonce() {
        $return = '';
        for ($i = 0; $i < 32; $i++) {
            $return .= chr(mt_rand(0, 255));
        }
        return base64_encode(hash('sha256', $return, true));
    }

    private function startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}
