<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */

namespace Princeton\App\Strings\Internationalizer;

use Princeton\App\Strings\Internationalizer;

/**
 * Provides internationalization support with simple positional variable substitution.
 */
class Positional extends Internationalizer
{
    protected $replaceStr = "%";

    /**
     * Looks up the first argument in the lookup file, then substitutes the
     * other args into that string, replacing each instance of $replaceStr ("%" by default).
     * Substitutions may be given as a single array argument or as a list of individual arguments.
     *
     * Examples:
     * $ordinal->get('stringName', 'replaces 1st %', 'replaces 2nd %');
     * $ordinal->get('stringName', [ 'replaces 1st %', 'replaces 2nd %'] );
     *
     * @param string $string The string name to look up in the internationalization lookup file.
     * @param ...string $subs Arbitrary list of substitution variables to be applied via sprintf()
     * @return string
     * @see Internationalizer::get()
     * @see \array_map() -
     *     "construct an array of arrays ...  by using NULL as the ... callback"
     */
    public function get($string)
    {
        $format = parent::get($string);
        $subs = func_get_args();
        array_shift($subs);

        if (sizeof($subs) == 1 && is_array($subs[0])) {
            $subs = $subs[0];
        }

        $parts = explode($this->replaceStr, $format);
        
        if (sizeof($subs) >= sizeof($parts)) {
            $subs = array_slice($subs, 0, sizeof($parts)-1);
        }

        return implode(array_map('implode', array_map(null, $parts, $subs)));
    }

    /**
     * Set the character/string to use as the replacement string, instead of "%".
     *
     * @param string $string
     */
    public function setReplaceStr($string)
    {
        $this->replaceStr = $string;
    }
}
