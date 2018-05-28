<?php

namespace Princeton\App\Slim;

use Slim\Container as SlimContainer;
use League\Container\Container as LeagueContainer;
use League\Container\ReflectionContainer as LeagueReflectionContainer;
use Princeton\App\Cache\Cache;
use Princeton\App\Cache\ArrayCache;
use Princeton\App\Config\Configuration;
use Princeton\App\Config\NullConfiguration;
use Princeton\App\Platform\Platform;
use Princeton\App\Platform\PrincetonPlatform;

/**
 * An extension of the Slim Container class which by default provides
 * an Autowire container based on the PHPLeague ReflectionContainer.
 * With autowire = false, it instead provides
 * PHPLeague Container functionality via delegation,
 * which includes a fallback delegate Reflection Container.
 * The Reflection Container will produce shared objects
 * if the 'settings' constructor option 'singletonReflection'
 * is set to true.
 * If doing autowiring, it also sets up default injection assignments:
 *      Cache => ArrayCache
 *      Configuration => NullConfiguration
 *      Platform => PrincetonPlatform
 * which may be overridden either via environment variables
 * (SLIMCONFIG_CACHE_CLASS, SLIMCONFIG_CONFIGURATION_CLASS and SLIMCONFIG_PLATFORM_CLASS, respectively)
 * or in the SlimConfig configuration property config.container.injections.
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

            /* Set some standard default injection assignments */
            $this->assign(
                Cache::class,
                getenv('SLIMCONFIG_CACHE_CLASS') ?: ArrayCache::class
            );
            $this->assign(
                Configuration::class,
                getenv('SLIMCONFIG_CONFIG_CLASS') ?: NullConfiguration::class
            );
            $this->assign(
                Platform::class,
                getenv('SLIMCONFIG_PLATFORM_CLASS') ?: PrincetonPlatform::class
            );

            /* Set any configured injection assignments */
            foreach ($values['injections'] ?? [] as $interface => $name) {
                $this->assign($interface, $name);
            }
        } else {
            $reflectionDelegate = (
                $this->hasSetting('singletonReflection')
                ? new ReflectionContainer()
                : new LeagueReflectionContainer()
            );
            $reflectionDelegate->setContainer($this);
            $this->delegate = (new LeagueContainer())->delegate($reflectionDelegate);
        }
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
