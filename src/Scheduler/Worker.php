<?php

namespace Princeton\App\Scheduler;

class Worker
{
	public static function handleSignals($func)
	{
		declare(ticks = 1) {
			// Bind a callback on the SIGTERM signal.
			pcntl_signal(SIGTERM,
				function ($signo)
				{
					error_log("Exiting on SIGTERM.\n");
					exit(0);
				}
			);
			$func();
		}
	}
}
