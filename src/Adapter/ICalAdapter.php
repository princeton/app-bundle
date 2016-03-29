<?php

namespace Princeton\App\Adapter;

abstract class ICalAdapter extends HttpAdapter
{
	private static $dateFields = array('DTSTART', 'DTEND', 'DTSTAMP', 'CREATED', 'LAST-MODIFIED');
	
	/* Does not support VTODO's (but probably could easily enough). */
	public function parse($data)
	{
		$ical = new ICal($data);
		$events = $ical->events();

		foreach ($events as &$event) {
			foreach ((self::$dateFields) as $field) {
				if (isset($event[$field])) {
					$event[$field] = new \DateTime('@'.$ical->iCalDateToUnixTimestamp($event[$field]));
				}
			}
		}
		
		return $events;
	}
}
