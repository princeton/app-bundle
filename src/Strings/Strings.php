<?php

namespace Princeton\App\Strings;

/**
 * The interface of objects managed with the Strings trait.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
interface Strings
{
	public function getLanguage();
	public function getMapping();
	public function get($string);
}
