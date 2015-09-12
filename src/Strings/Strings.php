<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */

namespace Princeton\App\Strings;

/**
 * The interface of objects managed with the Strings trait.
 */
interface Strings
{
	public function getLanguage();
	public function getMapping();
	public function get($string);
	
	public function setLanguage($language);
}
