<?php

namespace Princeton\App\Strings;

use Slim\Slim;
use Princeton\App\Cache\CachedYaml;
use Princeton\App\Traits\Authenticator;
use Princeton\App\Traits\AppConfig;
use Symfony\Component\Yaml\Parser;

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
			if (isset($data['$include-file'])) {
				$yaml = new Parser();
				$incFile = $this->languagePath() . '/' . $data['$include-file'];
				$incData = $yaml->parse(file_get_contents($incFile));
				$include = $flatten($incData, $prefix);
				$strings = $strings + $include;
				unset($data[$key]);
			}
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
		$this->strings = $cachedStrings->fetch($file);
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
