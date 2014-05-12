<?php

namespace Princeton\Injection;

class StandardDependencyManager extends DependencyManager
{
	public function __construct()
	{
		$this->addRule('platform', new EnvInjector('PRIN_PLATFORM_CLASS', '\Princeton\Platform\Platform'));
		$this->addRule('cache', new EnvInjector('PRIN_CACHE_CLASS', '\Doctrine\Common\Cache\Cache', '\Doctrine\Common\Cache\ArrayCache'));
		$this->addRule('appConfig', new EnvInjector('PRIN_CONFIG_CLASS', '\Princeton\Config\Configuration', '\Princeton\Config\NullConfiguration'));
		$this->addRule('authenticator', new ConfigInjector('classes.authenticator', '\Princeton\Authentication\Authenticator'));
		$this->addRule('strings', new ConfigInjector('classes.strings', '\Princeton\Strings\Strings'));
	}
}
