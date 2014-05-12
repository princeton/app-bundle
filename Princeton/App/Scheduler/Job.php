<?php

namespace Princeton\App\Scheduler;

/**
 * Abstraction of a Scheduler job.
 *
 * @author Kevin Perry, perry@princeton.edu
 *
 * @copyright 2014 The Trustees of Princeton University.
 */
interface Job
{
	public function name();
	public function perform();
}
