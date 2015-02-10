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
 * 		'a9b8c7deadbeef4321': {name:'My Nexus 10',token:'fedcba987654321'},
 * }}
 *
 * The server-side 'token' value is a has of the client's token.
 * (this fact is transparent to the getToken/setToken API.)
 *
 * Need a UI page to choose "Remember me",
 * and one to manage (i.e. delete) configured devices individually or "all".
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */
abstract class RememberMeAuthenticator implements Authenticator
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
     * Inactivity timeout on user sessions, in seconds.
     */
    const SESSION_TTL = 3600;

    /*
     * How long until tokens stored in the database become invalid.
     * One year = 315360005 seconds.
     */
    const TOKEN_TTL = 315360005;
    
    /*
     * Cached user object (application-specific).
     */
    private $user = false;

    /**
     * To be implemented by subclass.
     * Get the currently valid token, if any, for the given device,
     * from the application.
     *
     * @param string $username The user's name.
     * @param string $device The device ID.
     * @return string The stored token, or null if there is none.
     */
    abstract public function getToken($username, $device);

    /**
     * To be implemented by subclass.
     * Tell the application to store the currently valid token for the given device.
     *
     * @param string $username The user's name.
     * @param string $device The device ID.
     * @param string $token The new token to save.
     * @return void
     */
    abstract public function setToken($username, $device, $token);

    /**
     * Test whether we can authenticate the user.
     *
     * @see \Princeton\App\Authentication\Authenticator::authenticate()
     */
    public function authenticate()
    {
        if (!$this->user) {
        	session_start();
        	$now = time();
        	$expired = $now - self::SESSION_TTL;
        	
        	if (isset($_SESSION['RMAUTH_LAST']) && $_SESSION['RMAUTH_LAST'] < $expired) {
        	    session_unset();
        	    session_destroy();
        	}
        	$_SESSION['RMAUTH_LAST'] = $now;
        	
        	if (isset($_SESSION['RMAUTH_USER'])) {
        		// There is an active session.
        		$this->user = new \stdClass();
        		$this->user->username = $_SESSION['RMAUTH_USER'];
        	} else {
        		// Attempt to do login authentication.
                $cookie = $this->clientCookie();
                
                if ($cookie) {
                	$cToken = $this->hash($cookie['token']);
                    $data = $this->getToken($cookie['user'], $cookie['device']);
                    list ($sToken, $sTime) = $this->decodeServerToken($data);
                    $expired = $now - self::TOKEN_TTL;
                    
                    if ($cToken === $sToken && $sTime > $expired) {
                        // Matching, valid token - authenticated login.
                        // Cache user's id and set up new login token.
                        $this->user = new \stdClass();
                        $this->user->username = $cookie['user'];
                        $_SESSION['RMAUTH_USER'] = $cookie['user'];
                        $this->setupTokens($cookie['user'], $cookie['device']);
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
            
            session_write_close();
    	}
        
        return $this->user;
    }

    /**
     * For MultiAuthenticator.  Runs after user has been authenticated
     * by some auth module lower in the stack.
     * @param mixed $user
     */
    public function postAuthenticated($user)
    {
        $this->configureDeviceUser($user->{'username'}, false);
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
        if (!empty($username)) {
            $cookie = $this->clientCookie();
            
            if ($cookie) {
                if ($username == $cookie['user']) {
                    $device = $cookie['device'];
                    $token = $this->getToken($username, $device);
                    if ($cookie['token'] === '-none-') {
                    	// Compromised cookie or expired token,
                    	// but user has re-authenticated.
                        $this->setupTokens($username, $device);
                    }
                }
            } elseif (!empty($_COOKIE[self::COOKIE_NAME_DEV])) {
            	$device = $_COOKIE[self::COOKIE_NAME_DEV];
                $token = $this->getToken($username, $device);
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
    	$this->setToken($username, $device, $tokenData);
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
     * the cookie, if $cookie is null.
     * @param array $cookie
     */
    private function setClientCookie($cookie)
    {
    	if ($cookie) {
        	$cookieString = json_encode($cookie);
        	setcookie(self::COOKIE_NAME, $cookieString, null, '/', null, true, true);
    	} else {
    		setcookie(self::COOKIE_NAME, '', 1, '/');
    	}
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
}
