<?php

namespace Princeton\Injection;

/**
 * The EnvInjector is an Injector which
 * looks up its classes in the environment.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class EnvInjector extends Injector {
	protected function lookup($name) {
		return getenv($name);
	}
}
