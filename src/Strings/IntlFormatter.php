<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */

namespace Princeton\App\Strings;

/**
 * Provides internationalization support with sprintf()-style variable substitution.
 */
class IntlFormatter extends Internationalizer
{
    /**
     * @see \sprintf()
     * @see Internationalizer::get()
     * @param string $string The string name to look up in the internationalization lookup file.
     * @param ...string $subs Arbitrary list of substitution variables to be applied via sprintf()
     * @return string
     */
	public function get($string)
    {
            $format = parent::get($string);
            $subs = func_get_args();
            array_shift($subs);
            return vsprintf($format, $subs);
    }
}
