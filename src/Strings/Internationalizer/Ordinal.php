<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */

namespace Princeton\App\Strings\Internationalizer;

use Princeton\App\Strings\Internationalizer;

/**
 */
class Ordinal extends Internationalizer
{
    protected $prefix = "%";

    /**
     * Provides internationalization support with simple ordinal variable substitution.
     *
     * Substitutions may be given as a single array argument or as a list of individual arguments.
     * Translates each "%n" in the string into a sprintf-style "%n$s", and then uses sprintf
     * to perform variable substitutions.
     *
     * Examples:
     * $ordinal->get('stringName', 'replaces %1', 'replaces %2');
     * $ordinal->get('stringName', [ 'replaces %1', 'replaces %2'] );
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
        
        if (sizeof($subs) == 1 && is_array($subs[0])) {
            $subs = $subs[0];
        }

        $regex = '/' . $this->prefix . '[0-9]+|({[0-9]+})/';
        $format = preg_replace($regex, '$0$s',  $format);
        
        return vsprintf($format, $subs);
    }

    /**
     * Set the character/string to use as the replacement prefix,
     * instead of "%".  Characters that are special to PCRE regex
     * processing should not be used.
     *
     * @param string $string
     */
    public function setPrefix($string)
    {
        $this->prefix = $string;
    }
}
