<?php
/**
  * The CSP class provides an interface for creating and applying
  * a Content-Security-Policy header string from an array describing
  * the desired policy rules.
  *
  * @author Kevin Perry, perry@princeton.edu
  * @copyright 2016 The Trustees of Princeton University
  */

namespace Princeton\App;

class CSP
{
    const NONE = "'none'";
    const SELF = "'self'";
    const UNSAFE_EVAL = "'unsafe-eval'";
    const UNSAFE_INLINE = "'unsafe-inline'";

    const ALLOW_FORMS = 'allow-forms';
    const ALLOW_SAME_ORIGIN = 'allow-same-origin';
    const ALLOW_SCRIPTS = 'allow-scripts';
    const ALLOW_TOP_NAVIGATION = 'allow-top-navigation';

    const DEFAULT_RULES = [
        'default-src'     => self::NONE,
        'base-uri'        => CSP::SELF,
        'form-action'     => CSP::SELF,
        'frame-ancestors' => CSP::NONE,
        'plugin-types'    => '',
    ];

    /**
     * @var string
     */
    private $value = '';
    
    /**
     * Defaults to the most stringent possible policy.
     *
     * @param $rules array An array describing the desired source rules.
     *      Keys are any valid directive.
     *      Values are either a string or an array of strings to be concatenated.
     */
    public function __construct($rules = [])
    {
        $arr = [];

        $rules = array_merge(self::DEFAULT_RULES, $rules);

        foreach ($rules as $type => $rule) {
            $arr[] = "$type " . (is_array($rule) ? implode(' ', $rule) : $rule);
        }

        $this->value = implode('; ', $arr);
    }

    public function __toString()
    {
        return $this->value;
    }

    public function setHeader($reportOnly = false)
    {
        $reportOnly = $reportOnly ? '-Report-Only' : '';
        header("Content-Security-Policy$reportOnly: " . $this->value);
    }
}
