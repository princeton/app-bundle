<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */

namespace Princeton\App\Strings\Internationalizer;

use Princeton\App\Strings\Internationalizer;

/**
 * Provides internationalization support with sprintf()-style variable substitution.
 */
class Sprintf extends Internationalizer
{
    /**
     * Substitutions may be given as a single array argument or as a list of individual arguments.
     *
     * Examples:
     * $ordinal->get('stringName', 'replaces %1$', 'replaces %2$');
     * $ordinal->get('stringName', [ 'replaces %1$', 'replaces %2$'] );
     *
     * @param string $string The string name to look up in the internationalization lookup file.
     * @param ...string $subs Arbitrary list of substitution variables to be applied via sprintf()
     * @return string
     * @see Internationalizer::get()
     * @see \sprintf()
     */
    public function get($string)
    {
        $format = parent::get($string);
        $subs = func_get_args();
        array_shift($subs);

        if (sizeof($subs) == 1 && is_array($subs)) {
            $subs = $subs[0];
        }

        return vsprintf($format, $subs);
    }
}
