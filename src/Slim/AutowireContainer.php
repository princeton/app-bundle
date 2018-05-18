<?php

namespace Princeton\App\Slim;

use InvalidArgumentException;
use League\Container\ReflectionContainer;
use Princeton\App\Injection\Injectable;

class AutowireContainer extends ReflectionContainer
{
    protected $shared = [];
    protected $assignments = [];

    public function get($name, array $args = [])
    {
        if (isset($this->shared[$name])) {
            return $this->shared[$name];
        } else {
            $name = $this->assignments[$name] ?? $name;

            if (isset(class_implements($name)[Injectable::class])) {
                return $this->shared[$name] = parent::get($name, $args);
            } else {
                throw new InvalidArgumentException(
                    "Autowired class '$name' must implement Injectable"
                );
            }
        }
    }

    public function has($name)
    {
        return parent::has($this->assignments[$name] ?? $name);
    }

    public function assign($name, $assignee)
    {
        if (isset(class_implements($assignee)[$name])) {
            $this->assignments[$name] = $assignee;
        } else {
            throw new InvalidArgumentException(
                "Assignee ($assignee) must implement name ($name)"
            );
        }
    }

    public function getInjections()
    {
        return [
            'shared' => array_keys($this->shared),
            'assignments' => array_keys($this->assignments),
        ];
    }
}
