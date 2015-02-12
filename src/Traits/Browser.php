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
	private $browser;
	
	/**
	 * Get a browser value from the browscap facility.
	 *
	 * @param string $name
	 * @return string
	 */
	public function getBrowserValue($name)
	{
		if (!$this->browser) {
    		try {
    			// In case no auto-session.
    		    session_start();
    		} catch (\Exception $ex) {
    		    // ignore.
    		}

    		$key = 'PU_BROWSCAP_BROWSER';
    		if (!isset($_SESSION[$key])) {
    		    $agent = $_SERVER['HTTP_USER_AGENT'];
    		    $_SESSION[$key] = @get_browser($agent);
    		}

    		$this->browser = $_SESSION[$key];
    		
    		if (empty($this->browser)) {
    			// ... so we know we've already tried.
    			$this->browser = new \stdClass();
    		}
		}
		
		return (isset($this->browser->{$name})) ? $this->browser->{$name} : 'unknown';
	}
}
