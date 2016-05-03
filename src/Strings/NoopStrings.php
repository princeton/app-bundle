<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */

namespace Princeton\App\Strings;

/**
 * Provides a test string translator for the Strings trait.
 * The string name is always returned untranslated.
 * Perhaps helpful to discover whether there are any strings in your
 * application that are not being passed through the lookup procedure.
 */
class NoopStrings implements Strings
{
	public function get($string)
	{
		return $string;
	}

	public function getLanguage()
	{
		return 'any';
	}

	public function getMapping()
	{
		return array();
	}

	public function setLanguage($language)
	{
		
	}
}
