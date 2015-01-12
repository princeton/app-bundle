<?php

namespace Princeton\App\Scheduler;

use Exception;

/**
 * A simple job scheduler.
 *
 * Subclasses must implement getUnprocessed().
 *
 * @author Kevin Perry, perry@princeton.edu
 *
 * @copyright 2014 The Trustees of Princeton University.
**/
abstract class Scheduler
{
	// Default to running once per minute.
	private $secsPerStep = 60;

	// Default to running on the minute.
	private $secsOffset = 0;

	/**
	 * @param int $when - Unix timestamp to compare job deadlines with.
	 * @return An array of Job objects.
	 */
	abstract function getUnprocessed($when);

	/*
	 * Run the Scheduler.
	 */
	public function run()
	{
		set_time_limit(0);
		$now = time();
		//while (true) {
			try {
				$this->processJobsScheduledBefore($now);
			} catch(Exception $ex) {
				try {
					$this->report('scheduler exception');
				} catch (Exception $ex2) {
					// ignore.
				}
			}

			// Times of the form hh:mm:00 are multiples of 60.
			$nextMinute = $this->secsPerStep*(intval($now/$this->secsPerStep) + 1) + $this->secsOffset;
			sleep(max(1, $nextMinute - time()));
			$now = $nextMinute;
		//}
	}

	/*
	 * Set our scheduling time-step.
	 */
	public function setTimeStep($secsPerStep)
	{
		$secs = intval($secsPerStep);
		if ($secs > 0) {
			$this->secsPerStep = $secs;
		}
	}

	/*
	 * Set our scheduling offset.
	 */
	public function setTimeOffset($secs)
	{
		$secs = intval($secs);
		if ($secs > 0) {
			$this->secsOffset = $secs;
		}
	}

	/*
	 * Process all the outstanding Jobs.
	 */
	private function processJobsScheduledBefore($now)
	{
		$jobs = $this->getUnprocessed($now);
		foreach ($jobs as $job) {
			$this->perform($job);
		}
	}

	/*
	 * Perform one Job.
	 */
	private function perform(Job $job)
	{
		try {
			$job->perform();
		} catch (Exception $ex) {
			$this->report('Scheduler: Exception while handling job "' . $job->name() . '"', $ex);
		}
	}

	/*
	 * Report an error.
	 */
	private function report($msg, $ex)
	{
		error_log($msg . ': ' . $ex); // , 3, 'scheduler.log');
	}
}
