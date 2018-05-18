<?php

namespace Princeton\App\Slim;

use League\Container\ReflectionContainer as LeagueContainer;

class ReflectionContainer extends LeagueContainer {
    protected $shared = [];

    public function get($alias, array $args = []) {
        if (isset($this->shared[$alias])) {
            return $this->shared[$alias];
        } else {
            $result = parent::get($alias, $args);

            if (is_string($result)) {
                $result = $this->getContainer()->get($result);
            }

            return $this->shared[$alias] = $result;
        }
    }
}
