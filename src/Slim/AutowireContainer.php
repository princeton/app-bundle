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
            $assignee = $this->assignments[$name] ?? $name;

            if (is_subclass_of($assignee, Injectable::class) && $this->has($assignee)) {
                return $this->shared[$name] = $this->shared[$assignee] = parent::get($assignee, $args);
            } else {
                throw new InvalidArgumentException(
                    "Autowired class '$assignee' must implement Injectable"
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
        $assignee = ($assignee[0] === '\\' ? substr($assignee, 1) : $assignee);
        $name = ($name[0] === '\\' ? substr($name, 1) : $name);

        if ($name === $assignee) {
            return;
        } elseif (is_subclass_of($assignee, $name)) {
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
