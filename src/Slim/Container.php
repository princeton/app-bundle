<?php

namespace Princeton\App\Slim;

use Slim\Container as SlimContainer;
use League\Container\Container as LeagueContainer;
use League\Container\ReflectionContainer as LeagueReflectionContainer;

/**
 * An extension of the Slim Container class which by default provides
 * an Autowire container based on the PHPLeague ReflectionContainer.
 * With autowire = false, it instead provides
 * PHPLeague Container functionality via delegation,
 * which includes a fallback delegate Reflection Container.
 * The Reflection Container will produce shared objects
 * if the 'settings' constructor option 'singletonReflection'
 * is set to true.
 */
class Container extends SlimContainer
{
    protected $delegate;

    public function __construct(array $values = [])
    {
        parent::__construct($values);

        if ($this->hasSetting('autowire')) {
            $this->delegate = new AutowireContainer();
            $this->delegate->setContainer($this);
            return;
        } elseif ($this->hasSetting('singletonReflection')) {
            $reflectionDelegate = new ReflectionContainer();
        } else {
            $reflectionDelegate = new LeagueReflectionContainer();
        }

        $reflectionDelegate->setContainer($this);
        $this->delegate = (new LeagueContainer())->delegate($reflectionDelegate);
    }

    /**
     * Delegates to PHPLeague Container::add()
     */
    public function add($alias, $concrete = null, $share = false)
    {
        return $this->delegate->add($alias, $concrete, $share);
    }

    public function assign($name, $assignee)
    {
        if ($this->delegate instanceof AutowireContainer) {
            /* @var $delegate AutowireContainer */
            $delegate = $this->delegate;
            return $delegate->assign($name, $assignee);
        }
    }

    public function has($id)
    {
        return parent::has($id) || $this->delegate->has($id);
    }

    public function get($id)
    {
        return parent::has($id) ? parent::get($id) : $this->delegate->get($id);
    }

    public function getInjections()
    {
        if ($this->delegate instanceof AutowireContainer) {
            /* @var $delegate AutowireContainer */
            $delegate = $this->delegate;

            return $delegate->getInjections();
        }
    }

    /**
     * Check for settings with default value of true.
     * @param unknown $name
     * @return boolean
     */
    protected function hasSetting($name)
    {
        return (
            !isset($this->get('settings')[$name])
            || $this->get('settings')[$name] == false
        );
    }
}
