<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014-2015 The Trustees of Princeton University.
 */

namespace Princeton\App\Strings;

use Slim\Slim;
use Princeton\App\Cache\CachedYaml;
use Princeton\App\Traits\AppConfig;

/**
 * Provides simple internationalization support for the Strings trait.
 * Application should provide appropriate lookup files in /assets/strings,
 * named e.g. "en-US.yml". These files are YAML format; they are loaded
 * with a YAML parser and then the resulting structure is flattened
 * for lookup efficiency.
 */
class Internationalizer implements Strings
{
    use AppConfig;

    protected $language = null;
    protected $strings = null;

    public function __construct($lang = null)
    {
        $this->setLanguage($lang);
    }

    public function get($string)
    {
        $this->load();

        return $this->strings[$string] ?? $string;
    }

    public function getLanguage()
    {
        $this->load();

        return $this->language;
    }

    public function getMapping()
    {
        $this->load();

        return $this->strings;
    }

    public function setLanguage($lang = null)
    {
        $found = false;

        if (!empty($lang)) {
            $file = $this->languageFile($lang);
            $found = file_exists($file);
            if (!$found) {
                $this->warn('Invalid language: ' . $lang);
            }
        }

        if (!$found) {
            $lang = $this->getAppConfig()->config('lang');
            $file = $this->languageFile($lang);
            $found = file_exists($file);
        }

        if (!$found) {
            $this->warn('Invalid default language: ' . $lang);
            $lang = 'en-US';
            $file = $this->languageFile($lang);
            $found = file_exists($file);

            if (!$found) {
                $this->warn('Cannot find any valid language files');
                $lang = null;
            }
        }

        if ($lang !== $this->language) {
            $this->language = $lang;
            $this->strings = null;
        }

        return $this;
    }

    protected function load()
    {
        if ($this->strings === null) {
            if ($this->language === null) {
                $this->warn('Language not set');

                return;
            }

            $file = $this->languageFile($this->language);

            $flatten = null;

            $flatten = function ($data, $prefix = '') use (&$flatten)
            {
                $strings = array();

                foreach ($data as $key => $value) {
                    if (is_array($data[$key])) {
                        $more = $flatten($data[$key], $prefix . $key . '.');
                        $strings = $strings + $more;
                    } else {
                        $strings[$prefix . $key] = $value;
                    }
                }

                return $strings;
            };

            $cachedStrings = new CachedYaml('I18n-', $flatten);
            $allStrings = $cachedStrings->fetch($file);

            /* NB: Only does include-files in top-level file!
             * The version that did fast recursive includes was
             * not able to check timestamps on included files
             * (see 10/10/14 commit f849b7d...)
             */
            foreach ($allStrings as $key => $value) {
                if (substr($key, -14) === '.$include-file') {
                    $prefix = substr($key, 0, -13);
                    $incFile = $this->languagePath($this->language) . '/' . $value;
                    $incReader = new CachedYaml(
                        'I18n-',
                        function ($data) use (&$flatten, $prefix) {
                            return $flatten($data, $prefix);
                        }
                    );
                    $allStrings += $incReader->fetch($incFile);
                }
            }

            $this->strings = $allStrings;
        }
    }

    protected function languagePath($lang)
    {
        return APPLICATION_PATH . '/assets/strings/' . $lang;
    }

    protected function languageFile($lang)
    {
        return $this->languagePath($lang) . '.yml';
    }

    protected function warn($message)
    {
        Slim::getInstance()->log->warning($message);
    }
}
