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
 * The server-side 'token' value is a hash of the client's token.
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
    const COOKIE_NAME2 = 'rmauth_device';

    /*
     * Name of the session variable to store our authenticated username in.
     */
    const USER_KEY = 'PU_RMAUTH_USER';

    /*
     * Name of the URL parameter for $cookiePath redirect.
     * See notes re $cookiePath below.
     */
    const REDIR_PARAM = 'rmauth_redirect';
    
    /*
     * This is of questionable utility. Should probably NOT ever set
     * rememberme.cookiePath, so that this defaults to '/'.
     * In that case, authentication procedes linearly.
     * The URL path for which the RememberMe cookies are valid.
     * If this is set to some other value, then it is used as the
     * cookie's path parameter, and we must
     * redirect there-and-back in order to get and process the
     * login authorization token. I will leave this functionality in for now,
     * but not use it.  To be considered further.
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
        $this->tokenTTL = empty($tokenTTL) ? 31536000 : $tokenTTL;

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

    public function isAuthenticated()
    {
        return !!$this->authenticate();
    }

    /**
     * Test whether we can authenticate the user.
     *
     * @see \Princeton\App\Authentication\Authenticator::authenticate()
     */
    public function authenticate()
    {
        if ($this->delegate && !$this->user) {
        	if (session_status() == PHP_SESSION_NONE) {
        	    session_start();
        	}
            $now = time();
            $expired = $now - $this->sessionTTL;
            $tskey = 'PU_RMAUTH_LAST';
            
            if (isset($_SESSION[$tskey]) && $_SESSION[$tskey] < $expired) {
            	$_SESSION = array();
            	if (ini_get('session.use_cookies')) {
            	    $params = session_get_cookie_params();
            	    setcookie(session_name(), '', 1,
            	    $params['path'], $params['domain'],
            	    $params['secure'], $params['httponly']
            	    );
            	}
                session_destroy();
            }
            
            if (isset($_SESSION[self::USER_KEY])) {
                // There is an active session.
                $this->user = new \stdClass();
                $this->user->username = $_SESSION[self::USER_KEY];
                $_SESSION[$tskey] = $now;
            } else {
                /* See notes re $this->cookiePath above. */
                if (
                	$this->cookiePath != '/'
                	&& !$this->match($_SERVER['REQUEST_URI'], $this->cookiePath)
                ) {
                    header('Location: ' . $this->cookiePath
                        . '?' . self::REDIR_PARAM
                        . '=' . urlencode($_SERVER['REQUEST_URI'])
                    );
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
                        $_SESSION[$tskey] = $now;
                        $this->setupTokens($cookie['user'], $cookie['device']);
                        
                        /* See notes re $this->cookiePath above. */
                        if ($this->cookiePath != '/' && isset($_REQUEST[self::REDIR_PARAM])) {
                            session_write_close();
                            header('Location: ' . $_REQUEST[self::REDIR_PARAM]);
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
        $idField = 'username';
        
        if ($this->delegate) {
            $this->configureDeviceUser($user->{$idField}, false);
            
            /* See notes re $this->cookiePath above. */
            if (
            	$this->cookiePath != '/'
            	&& isset($_REQUEST[self::REDIR_PARAM])
            ) {
                session_write_close();
                header('Location: ' . $_REQUEST[self::REDIR_PARAM]);
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
            } elseif (!empty($_COOKIE[self::COOKIE_NAME2])) {
                // No token cookie, but there is a device cookie.
                // Re-initialize.
                $device = $_COOKIE[self::COOKIE_NAME2];
                /*$token = */$this->delegate->getToken($username, $device);
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
        $_SESSION[self::USER_KEY] = $username;
        $token = $this->generateToken();
        $tokenData = $this->encodeServerToken($token);
        $this->delegate->setToken($username, $device, $tokenData);
        setcookie(self::COOKIE_NAME2, $device, time() + 99999999, '/');
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
        if (!is_array($info) || sizeof($info) != 2) {
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
     * the cookie and session if $cookie is null.
     * @param array $cookie
     */
    private function setClientCookie($cookie)
    {
        if ($cookie) {
            $value = json_encode($cookie);
            $expires = time() + $this->tokenTTL;
        } else {
            $value = '';
            $expires = 1;
            // TODO I thought this would help, but it kills CAS auth.
            // session_destroy();
        }
        setcookie(self::COOKIE_NAME, $value, $expires, $this->cookiePath, null, true);
        setcookie('rmauth_ok', ($value === '' ? 'no' : 'yes'), $expires, $this->cookiePath, null, true);
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

    /**
     * Returns whether $haystack starts with $needle.
     *
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    private function match($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}
