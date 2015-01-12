<?php

namespace Princeton\App\Strings;

use Slim\Slim;
use Princeton\App\Cache\CachedYaml;
use Princeton\App\Traits\Authenticator;
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
	use Authenticator, AppConfig;
	
	protected $language;
	protected $strings = array();

	public function __construct()
	{
		$user = $this->getAuthenticator()->getUser();
		if ($user && isset($user->{'lang'})) {
			$this->language = $user->{'lang'};
			$file = $this->languageFile();
			if (!file_exists($file)) {
				unset($file);
				Slim::getInstance()->log->warning('Invalid user lang for user.');
			}
		}
		
		if (!isset($file)) {
			$this->language = $this->getAppConfig()->config('lang');
			$file = $this->languageFile();
			if (!isset($this->language) || !file_exists($file)) {
				Slim::getInstance()->log->warning('Invalid default language: ' . $this->language);
				$this->language = 'en_US';
				$file = $this->languageFile();
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
				$incFile = $this->languagePath() . '/' . $value;
				$incReader = new CachedYaml('I18n-', function ($data) use (&$flatten, $prefix) { return $flatten($data, $prefix); });
				$allStrings += $incReader->fetch($incFile);
			}
		}
		
		$this->strings = $allStrings;
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
	
	private function languagePath()
	{
		return APPLICATION_PATH . '/assets/strings/' . $this->language;
	}
	
	private function languageFile()
	{
		return $this->languagePath() . '.yml';
	}
}
