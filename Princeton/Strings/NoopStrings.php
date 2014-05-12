<?php

namespace Princeton\Strings;

/**
 * Provides a test string translator for the Strings trait.
 * The string name is always returned untranslated.
 * Perhaps helpful to discover whether there are any strings in your
 * application that are not being passed through the lookup procedure.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
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
}
