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
		$calZone = $ical->timezone();

		foreach ($events as &$event) {
		    if (isset($event['X-WR-TIMEZONE'])) {
		        $timezone = new \DateTimeZone($event['X-WR-TIMEZONE']);
		    } elseif (isset($event['TZID'])) {
		        $timezone = new \DateTimeZone($event['TZID']);
		    } else {
		        $timezone = $calZone;
		    }

			foreach ((self::$dateFields) as $field) {
				if (isset($event[$field])) {
					$event[$field] = new \DateTime('@'.$ical->iCalDateToUnixTimestamp($event[$field]));

					if ($timezone) {
					    $event[$field]->setTimezone($timezone);
					}
				}
			}
		}

		return $events;
	}
}
