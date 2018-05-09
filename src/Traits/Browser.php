<?php

namespace Princeton\App\Traits;

/**
 * Browser allows you to easily look up browser characteristics
 * using get_browser() and the "browscap" system.  Just use this trait,
 * and then call, for example, $this->getBrowserValue('browser_brand_name');
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015, 2016 The Trustees of Princeton University.
 */
trait Browser
{
    private static $key = 'PU_BROWSCAP_BROWSER';

    private static $numericKeys = [
        'browser_bits',
        'platform_bits',
        'cssversion',
    ];

    private static $booleanKeys = [
        // These have all been deprecated.
        //'frames', 'iframes', 'tables', 'cookies', 'win16', 'win32', 'win64',
        //'javascript', 'javaapplets', 'alpha', 'beta', 'backgroundsounds', 'vbscript',
        //'activexcontrols', 'ismobiledevice', 'istablet', 'issyndicationreader', 'crawler',
    ];

    /**
     * Get a browser value from the browscap facility.
     *
     * @param string $name
     * @return mixed
     */
    public function getBrowserValue($name)
    {
        $this->initialize();
        return $_SESSION[self::$key][$name] ?? 'unknown';
    }

    /**
     * Get all the browser info from the browscap facility.
     *
     * @return object
     */
    public function getBrowserValues()
    {
        $this->initialize();
        return $_SESSION[self::$key] ?? [];
    }

    protected function initialize()
    {
        if (empty($_SESSION[self::$key])) {
            try {
                // In case no auto-session.
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
            } catch (\Exception $ex) {
                // ignore.
            }

            if (empty($_SESSION[self::$key])) {
                try {
                    $info = get_browser(null, true);

                    unset($info['browser_name_regex']);

                    foreach (self::$numericKeys as $name) {
                        $info[$name] = 0 + @$info[$name];
                    }

                    foreach (self::$booleanKeys as $name) {
                        $info[$name] = !!@$info[$name];
                    }

                    $_SESSION[self::$key] = $info;
                } catch (\Exception $ex) {
                    error_log('Thrown during get_browser: ' . $ex->getMessage());
                }
            }
        }
    }
}
