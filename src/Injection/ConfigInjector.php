<?php

namespace Princeton\App\Injection;

use Princeton\App\Traits\AppConfig;

/**
 * The ConfigInjector is an Injector which
 * looks up its classes in the Configuration object.
 *
 * @deprecated
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class ConfigInjector extends Injector {
	use AppConfig;
	
	protected function lookup($name) {
		return $this->getAppConfig()->config($name);
	}
}
