<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */

namespace Princeton\App\Strings;

/**
 * Provides a test string translator for the Strings trait.
 * All known strings are mapped to "___".
 * Unrecognized string names will be returned untranslated.
 * Perhaps helpful to discover whether there are any strings in your
 * application that are not being passed through the lookup procedure.
 */
class EmptyStrings extends Internationalizer
{
	protected $strings;
	
	public function __construct()
	{
		parent::__construct();
		foreach (($this->strings) as $key => $value) {
			$this->strings[$key] = '___';
		}
	}
}
