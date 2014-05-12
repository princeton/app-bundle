<?php

namespace Princeton\App\Injection;

class StandardDependencyManager extends DependencyManager
{
	public function __construct()
	{
		$this->addRule('platform', new EnvInjector('PRIN_PLATFORM_CLASS', '\Princeton\App\Platform\Platform'));
		$this->addRule('cache', new EnvInjector('PRIN_CACHE_CLASS', '\Doctrine\Common\Cache\Cache', '\Doctrine\Common\Cache\ArrayCache'));
		$this->addRule('appConfig', new EnvInjector('PRIN_CONFIG_CLASS', '\Princeton\App\Config\Configuration', '\Princeton\App\Config\NullConfiguration'));
		$this->addRule('authenticator', new ConfigInjector('classes.authenticator', '\Princeton\App\Authentication\Authenticator'));
		$this->addRule('strings', new ConfigInjector('classes.strings', '\Princeton\App\Strings\Strings'));
	}
}
