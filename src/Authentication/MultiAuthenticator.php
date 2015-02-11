<?php

namespace Princeton\App\Authentication;

use Princeton\App\Traits;

/**
 * An authenticator that allows you to stack several authenticators.
 *
 * Expects the following configuration:
 * authenticators: [ "class1", "class2", ... ]
 * Authenticators that implement an afterAuthenticated($user) method
 * will have it called when control returns up the stack of authenticators
 * if the user has been successfully authenticated.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class MultiAuthenticator implements Authenticator
{
    use Traits\AppConfig;

    protected $user = null;
    
    protected $exception = null;
    
    public function authenticate()
    {
    	if (empty($this->user)) {
	        /* @var $conf \Princeton\App\Config\Configuration */
	        $conf = $this->getAppConfig();
	        $authenticators = $conf->config('authenticators');
	        
	        if ($authenticators) {
	            $this->user = $this->checkAuths($authenticators);
	        }
            if (!$this->user && $this->exception) {
            	throw $this->exception;
            }
    	}
    	return $this->user;
    }

    protected function checkAuths($list)
    {
        $user = false;
        $class = array_shift($list);
        if (!empty($class)) {
            /* @var $obj \Princeton\App\Authentication\Authenticator */
            $obj = new $class();
            try {
	            $user = $obj->authenticate();
            } catch (\Exception $ex) {
            	$this->exception = $ex;
            }
            if (!$user) {
                $user = $this->checkAuths($list);
            }
            
            // If the authenticator implements an afterAuthenticated() method, run it now.
            if ($user && method_exists($obj, 'afterAuthenticated')) {
            	$obj->{'afterAuthenticated'}($user);
            }
        }
        return $user;
    }
}
