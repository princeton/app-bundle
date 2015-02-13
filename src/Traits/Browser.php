<?php

namespace Princeton\App\Traits;

/**
 * Browser allows you to easily look up browser characteristics
 * using get_browser() and the "browscap" system.  Just use this trait,
 * and then call, for example, $this->getBrowserValue('browser_brand_name');
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */
trait Browser
{
	/**
	 * Get a browser value from the browscap facility.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getBrowserValue($name)
	{
    	$key = 'PU_BROWSCAP_BROWSER';
    	
		if (!isset($_SESSION[$key])) {
    		try {
    			// In case no auto-session.
            	if (session_status() == PHP_SESSION_NONE) {
            	    session_start();
            	}
    		} catch (\Exception $ex) {
    		    // ignore.
    		}

    		if (!isset($_SESSION[$key])) {
    		    $agent = $_SERVER['HTTP_USER_AGENT'];
    		    try {
        		    $_SESSION[$key] = get_browser($agent);
        		    foreach (array('browser_bits', 'platform_bits', 'cssversion') as $name) {
	        		    $_SESSION[$key]->{$name} = 0 + @$_SESSION[$key]->{$name};
        		    }
        		    foreach (array(
        		    	'frames', 'iframes', 'tables', 'cookies', 'win16', 'win32', 'win64',
        		    	'javascript', 'javaapplets', 'alpha', 'beta', 'backgroundsounds', 'vbscript',
        		    	'activexcontrols', 'ismobiledevice', 'istablet', 'issyndicationreader', 'crawler'
        		    ) as $name) {
        		    	$_SESSION[$key]->{$name} = !!@$_SESSION[$key]->{$name};
        		    }
    		    } catch (\Exception $ex) {
        			// ... so we know we've already tried.
        			$_SESSION[$key] = new \stdClass();
    		    }
    		}
		}
		
		return (isset($_SESSION[$key]->{$name})) ? $_SESSION[$key]->{$name} : 'unknown';
	}

	/**
	 * Get a browser value from the browscap facility.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getBrowserValues()
	{
		$name = $this->getBrowserValue('browser');
		return (array) $_SESSION['PU_BROWSCAP_BROWSER'];
	}
}
