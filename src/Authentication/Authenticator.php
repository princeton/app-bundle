<?php

namespace Princeton\App\Authentication;

/**
 * A simple authentication interface.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
interface Authenticator
{
	public function getUser();
	public function getUsername();
}
