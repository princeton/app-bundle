<?php

namespace Princeton\Scheduler;


/**
 * Abstraction of a Scheduler job.
 *
 * @author Kevin Perry, perry@princeton.edu
 *
 * @copyright 2014 The Trustees of Princeton University.
 */
abstract class BaseJob implements Job
{
	private $name;
	protected $params;

	public function __construct($name, $params)
	{
		$this->name = $name;
		$this->params = $params;
	}

	public function name(){
		return $this->name;
	}
}
