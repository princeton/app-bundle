<?php

namespace Princeton\App\Injection;

use Princeton\App\Exceptions\DependencyException;

/**
 * Typical usage:
 * 	StandardDependencyManager::register(); // ... or some other, app-specific subclass.
 * 	...
 * 	trait Foo {
 * 		protected function getFoo() {
 * 			DependencyManager::get('foo');
 * 		}
 * 	}
 */
class DependencyManager
{
	private static $manager;
	
	private $rules = array();
	private $default;

	/**
	 * Creates and registers an instance of the calling class as the default dependency manager,
	 * which will be used by DependencyManager::get($name).  This should normally be called only once.
	 * Use $force=true to override.
	 * 
	 * @param $force Whether to allow this to replace an already existing default manager.
	 * 
	 * @throws \Princeton\App\Exceptions\DependencyException
	 */
	public static function register($force = false)
	{
		if (isset(self::$manager)) {
			throw new DependencyException('Dependency Manager already registered!');
		}
		$class = get_called_class();
		self::$manager = new $class;
	}
	
	/**
	 * Gets the dependency object associated with $name.
	 * 
	 * @param unknown $name
	 */
	public static function get($name)
	{
		return self::$manager->inject($name);
	}
	
	/**
	 * To override the registered manager, explicitly create a new manager and use its inject() method to retrieve objects.
	 * Calling $manager->inject($name) is just like calling DependencyManager::get($name), only it uses $manager's ruleset
	 * instead of the ruleset of the default manager.
	 * 
	 * @param string $name
	 * @return an object, or null if no object found.
	 */
	public function inject($name)
	{
		if (isset($this->rules[$name])) {
			return $this->rules[$name]->getInjected();
		} else if (isset($this->default)) {
			return $this->default->getInjected($name);
		} else {
			return null;
		}
	}
	
	/**
	 * Adds a rule to the Dependency Manager's rule list.  Use in subclass constructor to extend the ruleset.
	 * Once a rule has been set for $name, DependencyManager::get($name) will return $injector->getInjected();
	 * 
	 * @param string $name
	 * @param Injector $injector
	 */
	protected function addRule($name, Injector $injector)
	{
		$this->rules[$name] = $injector;
		$injector->setManager($this);
	}
	
	/**
	 * Set the default injector to be used for names which do not have an associated injector rule.
	 * In this case, DependencyManager::get($name) will return $injector->getInjected($name);
	 * 
	 * @param Injector $injector
	 */
	protected function setDefaultInjector(Injector $injector)
	{
		$this->default = $injector;
		$injector->setManager($this);
	}
}
