<?php

namespace Princeton\App\Traits;

/**
 * AppConfig uses DependencyManager to supply a Configuration object to its class.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
trait AppConfig
{
    /**
     * Get a Configuration object.
     *
     * @return \Princeton\App\Config\Configuration
     */
    public function getAppConfig()
    {
        return \Princeton\App\Injection\DependencyManager::get('appConfig');
    }
}
