<?php

namespace Princeton\App\Authentication;

use Princeton\App\Config\Configuration;
use Princeton\App\Injection\Injectable;

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
class MultiAuthenticator implements Authenticator, Injectable
{
    protected $user = null;
    
    protected $exception = null;

    protected $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }
    
    public function isAuthenticated()
    {
    	if (empty($this->user)) {
            $result = false;
	        $authenticators = $this->config->config('authenticators');

	        if ($authenticators) {
	            foreach ($authenticators as $class) {
                    if (!empty($class)) {
                        /* @var $obj \Princeton\App\Authentication\Authenticator */
                        $obj = new $class();
            
                        try {
	                        $result = $obj->isAuthenticated();
                        } catch (\Exception $ex) {
                        }
                    }

                    if ($result) {
                        break;
                    }
                }
	        }

            return $result;
    	}

    	return true;
    }

    public function authenticate()
    {
    	if (empty($this->user)) {
	        $authenticators = $this->config->config('authenticators');
	        
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
            
            $method = 'afterAuthenticated';
            
            // If the authenticator implements an afterAuthenticated() method, run it now.
            if ($user && method_exists($obj, $method)) {
            	$obj->{$method}($user);
            }
        }

        return $user;
    }
}
