<?php

namespace Princeton\App\Strings;

use Slim\Slim;
use Princeton\App\Cache\CachedYaml;
use Princeton\App\Traits\AppConfig;

/**
 * Provides simple internationalization support for the Strings trait.
 * Application should provide appropriate lookup files in /assets/strings,
 * named e.g. "en_US.yml". These files are YAML format; they are loaded
 * with a YAML parser and then the resulting structure is flattened
 * for lookup efficiency.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class Internationalizer implements Strings
{
	use AppConfig;
	
	protected $language;
	protected $strings = array();

	public function __construct($lang = null)
	{
		$this->setLanguage($lang);
	}
	
	public function setLanguage($lang)
	{
		$found = false;
		if (!empty($lang)) {
			$file = $this->languageFile($lang);
			$found = file_exists($file);
			if (!$found) {
				Slim::getInstance()->log->warning('Invalid language: ' . $lang);
			}
		}
		if (!$found) {
			$lang = $this->getAppConfig()->config('lang');
			$file = $this->languageFile($lang);
			$found = file_exists($file);
		}
		if (!$found) {
			Slim::getInstance()->log->warning('Invalid default language: ' . $lang);
			$lang= 'en_US';
			$file = $this->languageFile($lang);
			$found = file_exists($file);
			if (!$found) {
				Slim::getInstance()->log->warning('Cannot find any valid language files';
			}
		}
		
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
		 * The version that did fast recursive includes was not able
		 * to check timestamps on included files (see 10/10/14 commit f849b7d...)
		 */
		foreach ($allStrings as $key => $value) {
			if (substr($key, -14) === '.$include-file') {
				$prefix = substr($key, 0, -13);
				$incFile = $this->languagePath($lang) . '/' . $value;
				$incReader = new CachedYaml('I18n-', function ($data) use (&$flatten, $prefix) { return $flatten($data, $prefix); });
				$allStrings += $incReader->fetch($incFile);
			}
		}
		
		$this->language = $lang;
		$this->strings = $allStrings;
		
		return $this;
	}
	
	public function get($string)
	{
		return (isset($this->strings[$string]) ? $this->strings[$string] : $string);
	}

	public function getLanguage()
	{
		return $this->language;
	}

	public function getMapping()
	{
		return $this->strings;
	}
	
	private function languagePath($lang)
	{
		return APPLICATION_PATH . '/assets/strings/' . $lang;
	}
	
	private function languageFile($lang)
	{
		return $this->languagePath($lang) . '.yml';
	}
}
