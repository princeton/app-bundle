<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */

namespace Princeton\App\Strings;

/**
 * Provides internationalization support with simple positional variable substitution.
 */
class IntlSubstituter extends Internationalizer
{
    public $replaceStr = "{}";

    /**
     * Looks up the first argument in the lookup file, then substitutes the
     * other args into that string, replacing each instance of $replaceStr ("{}" by default).
     *
     * @see Internationalizer::get()
     * @see \array_map() - 
     *     "construct an array of arrays ...  by using NULL as the ... callback"
     * @param string $string The string name to look up in the internationalization lookup file.
     * @param ...string $subs Arbitrary list of substitution variables to be applied via sprintf()
     * @return string
     */
	public function get($string)
    {
            $format = parent::get($string);
            $subs = func_get_args();
            array_shift($subs);
            $parts = explode($this->replaceStr, $format);
            if (count($subs) >= count($parts)) {
                $subs = array_slice($subs, 0, count($parts)-1);
            }
            $arr = array_map(null, $parts, $subs);
            return implode(array_map('implode', array_map(null, $parts, $subs)));
    }
}
