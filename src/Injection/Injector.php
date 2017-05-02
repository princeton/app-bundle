<?php

namespace Princeton\App\Injection;

use Princeton\App\Exceptions\DependencyException;

/**
 * The Injector class implements the Factory pattern
 * to inject a dependency into the using class.
 * It stores the created objects internally, and returns
 * the same object for all subsequent calls to get() or inject()
 * with the same name, thus the objects are effectively singletons.
 * Performs reflection on the object's constructor and does recursive
 * dependency injection to fulfill any required arguments.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
abstract class Injector
{
	private $injected = array();
	private $name;
	private $parentClass;
	private $fallbackClass;
	private $manager;

	/**
	 *
	 * @param $name name passed to $lookup to resolve the classname to be injected.
	 * @param $lookup a callable used to resolve the classname to be injected.
	 * @param $parentClass name of a superclass that the resolved class must inherit from.
	 * @param $fallbackClass alternate default class if classname resolution fails.
	 */
	public function __construct($name = null, $parentClass = null, $fallbackClass = null)
	{
		$this->name = $name;
		$this->parentClass = $parentClass;
		$this->fallbackClass = $fallbackClass;
	}
	
	/**
	 * Get the injected object. Calls lookup($name) to resolve $name into a classname,
	 * then instantiates the resulting class, and stores the object internally.
	 * If the lookup returns null, then it will use $parentClass
	 * (or $fallbackClass, if set) instead, as the name of the class to instantiate.
	 *
	 * @param $name name passed to $lookup to resolve the classname to be injected.
	 */
	public function getInjected($name = null)
	{
		$name = isset($name) ? $name : $this->name;
		if (!isset($this->injected[$name])) {
			$className = $this->lookup($name);
			if (!$className) {
				$className = $this->fallbackClass ? $this->fallbackClass : $this->parentClass;
			}

            if (!class_exists($className)) {
			    throw new DependencyException("Invalid $name - $className is not a class");
            }

			$args = array();
			$class = new \ReflectionClass($className);
			/* @var $constructor \ReflectionMethod */
			$constructor = $class->getConstructor();
			if (isset($constructor) && $constructor->getNumberOfRequiredParameters() > 0) {
				$parameters = array_slice($constructor->getParameters(), 0, $constructor->getNumberOfRequiredParameters());
				foreach ($parameters as $param) {
					$args[] = $this->manager->inject($param);
				}
			}
			
			$obj = $class->newInstanceArgs($args);
			if (!is_a($obj, $this->parentClass)) {
				throw new DependencyException('Invalid ' . $name . ' - not a subclass of ' . $this->parentClass);
			}
			$this->injected[$name] = $obj;
		}
		return $this->injected[$name];
	}
	
	public function hasInjected($name = null)
	{
		$name = isset($name) ? $name : $this->name;
		return isset($this->injected[$name]);
	}
	
	public function setManager(DependencyManager $manager)
	{
		if (isset($this->manager)) {
			throw new DependencyException('Injector\'s Dependency Manager has already been set.');
		}
		$this->manager = $manager;
	}
	
	abstract protected function lookup($name);
}
