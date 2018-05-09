<?php

namespace Test;

use Princeton\App\Config\Configuration;

/**
 * A Configuration that can be managed for testing.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */
class TestConfiguration implements Configuration
{
    /* Typical default values:
     *     classes.authenticator => Princeton\App\Authentication\MultiAuthenticator
     *     classes.strings => Princeton\App\Strings\Internationalizer
     */
    private $map = array(
        'classes.authenticator' => null,
        'classes.strings' => null,
    );

    public function config($name)
    {
        return $this->map[$name] ?? parent::config($name);
    }

	public function clearCached()
	{

	}
}
