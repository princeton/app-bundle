<?php

namespace Princeton\App\Adapter;

use DateTime;

/**
 * This PHP-Class should only read an iCal-File (*.ics), parse it and give an
 * array with its content.
 *
 * Majorly fixed by Kevin Perry 10/15/2013.
 *
 * PHP Version 5
 *
 * @category Parser
 * @package  Ics-parser
 * @author   Martin Thoma <info@martin-thoma.de>
 * @license  http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version  SVN: <svn_id>
 * @link     http://code.google.com/p/ics-parser/
 * @example  $ical = new ical('MyCal.ics');
 *           print_r( $ical->events() );
 */

/**
 * This is the iCal-class
 *
 * @category Parser
 * @package  Ics-parser
 * @author   Martin Thoma <info@martin-thoma.de>
 * @license  http://www.opensource.org/licenses/mit-license.php  MIT License
 * @link     http://code.google.com/p/ics-parser/
 *
 * @constructor
 */
class ICal
{
    /* How many ToDos are in this ical? */
    public  /** @type {int} */ $todo_count = 0;

    /* How many events are in this ical? */
    public  /** @type {int} */ $event_count = 0;

    /* The parsed calendar */
    public /** @type {Array} */ $cal;

    /* Which keyword has been added to cal at last? */
    private /** @type {string} */ $last_keyword;

    /**
     * Undoes iCal escaping.
     *
     * Added by Kevin Perry 10/15/2013.
     *
     * @param unknown_type $text
     * @return mixed
     */
    public function unescapeIcalText( $text ) {
    	$text = str_replace("\\\\", "\\", $text);
    	$text = str_replace('\,', ',', $text);
    	$text = str_replace('\;', ';', $text);
    	$text = str_replace('\n', "\n", $text);
    	$text = str_replace('\r', "\n", $text);
    	$text = str_replace("\r\n", "\n", $text);
    	
    	return $text;
    }

    /**
     * Creates the iCal-Object
     *
     * Majorly fixed by Kevin Perry 10/15/2013.
     *
     * @param {string} $content The iCal data
     *
     * @return Object The iCal-Object
     */
    public function __construct($content)
    {
        $content = trim($content);
        
        if (substr($content, 0, 15) !== 'BEGIN:VCALENDAR') {
            return false;
        } else {
            $type = '';
            $lines = explode("\n", str_replace("\n ", '', preg_replace('/[\r\n]+/', "\n", $content)));
            foreach ($lines as $line) {
                $line = $this->unescapeIcalText(trim($line));
                $add  = $this->keyValueFromString($line);
                if ($add === false) {
                    $this->addCalendarComponentWithKeyAndValue($type, false, $line);
                    continue;
                }

                list($keyword, $value) = $add;

                switch ($line) {
                // http://www.kanzaki.com/docs/ical/vtodo.html
                case "BEGIN:VTODO":
                    $this->todo_count++;
                    $type = "VTODO";
                    break;

                // http://www.kanzaki.com/docs/ical/vevent.html
                case "BEGIN:VEVENT":
                    //echo "vevent gematcht";
                    $this->event_count++;
                    $type = "VEVENT";
                    break;

                //all other special strings
                case "BEGIN:VCALENDAR":
                case "BEGIN:DAYLIGHT":
                    // http://www.kanzaki.com/docs/ical/vtimezone.html
                case "BEGIN:VTIMEZONE":
                case "BEGIN:STANDARD":
                    $type = $value;
                    break;
                case "END:VTODO": // end special text - goto VCALENDAR key
                case "END:VEVENT":
                case "END:VCALENDAR":
                case "END:DAYLIGHT":
                case "END:VTIMEZONE":
                case "END:STANDARD":
                    $type = "VCALENDAR";
                    break;
                default:
                    $this->addCalendarComponentWithKeyAndValue($type,
                                                               $keyword,
                                                               $value);
                    break;
                }
            }
            return $this->cal;
        }
    }

    /**
     * Add to $this->ical array one value and key.
     *
     * @param {string} $component This could be VEVENT, VCALENDAR, ...
     * @param {string} $keyword   The keyword, for example DTSTART
     * @param {string} $value     The value, for example 20110105T090000Z
     *
     * @return {None}
     */
    public function addCalendarComponentWithKeyAndValue($component,
                                                        $keyword,
                                                        $value)
    {
        if ($keyword == false) {
            $keyword = $this->last_keyword;
            switch ($component) {
            case 'VEVENT':
                $value = $this->cal[$component][$this->event_count - 1]
                                               [$keyword].$value;
                break;
            case 'VTODO' :
                $value = $this->cal[$component][$this->todo_count - 1]
                                               [$keyword].$value;
                break;
            }
        }

        if (stristr($keyword, "DTSTART") or stristr($keyword, "DTEND")) {
            $keyword = explode(";", $keyword);
            $keyword = $keyword[0];
        }

        switch ($component) {
        case "VTODO":
            $this->cal[$component][$this->todo_count - 1][$keyword] = $value;
            //$this->cal[$component][$this->todo_count]['Unix'] = $unixtime;
            break;
        case "VEVENT":
            $this->cal[$component][$this->event_count - 1][$keyword] = $value;
            break;
        default:
            $this->cal[$component][$keyword] = $value;
            break;
        }
        $this->last_keyword = $keyword;
    }

    /**
     * Get a key-value pair of a string.
     *
     * @param {string} $text which is like "VCALENDAR:Begin" or "LOCATION:"
     *
     * @return {array} array("VCALENDAR", "Begin")
     */
    public function keyValueFromString($text)
    {
        $matches = [];
        
        preg_match('/([^:]+)[:]([\w\W]*)/', $text, $matches);
        
        if (sizeof($matches) == 0) {
            return false;
        }
        
        return array_splice($matches, 1, 2);
    }

    /**
     * Return Unix timestamp from ical date time format
     *
     * TODO Does not support timezones!
     *
     * @param {string} $icalDate A Date in the format YYYYMMDD[T]HHMMSS[Z] or
     *                           YYYYMMDD[T]HHMMSS
     *
     * @return {int}
     */
    public function iCalDateToUnixTimestamp($icalDate)
    {
        $icalDate = str_replace('T', '', $icalDate);
        $icalDate = str_replace('Z', '', $icalDate);

        $pattern  = '/([0-9]{4})';   // 1: YYYY
        $pattern .= '([0-9]{2})';    // 2: MM
        $pattern .= '([0-9]{2})';    // 3: DD
        $pattern .= '([0-9]{0,2})';  // 4: HH
        $pattern .= '([0-9]{0,2})';  // 5: MM
        $pattern .= '([0-9]{0,2})/'; // 6: SS
        $date = [];
        
        preg_match($pattern, $icalDate, $date);

        // Unix timestamp can't represent dates before 1970
        if ($date[1] <= 1970) {
            return false;
        }
        // Unix timestamps after 03:14:07 UTC 2038-01-19 might cause an overflow
        // if 32 bit integers are used.
        $timestamp = mktime((int)$date[4],
                            (int)$date[5],
                            (int)$date[6],
                            (int)$date[2],
                            (int)$date[3],
                            (int)$date[1]);
        return  $timestamp;
    }

    /**
     * Returns an array of arrays with all events. Every event is an associative
     * array and each property is an element it.
     *
     * @return {array}
     */
    public function events()
    {
        $array = $this->cal;
        return $array['VEVENT'];
    }

    /**
     * Returns a boolean value whether thr current calendar has events or not
     *
     * @return {boolean}
     */
    public function hasEvents()
    {
        return (sizeof($this->events()) > 0 ? true : false);
    }

    /**
     * Returns the events in the given range.
     *
     * Majorly fixed by Kevin Perry 10/15/2013.
     *
     * @param {boolean} $rangeStart start date string
     * @param {boolean} $rangeEnd   end date string
     *
     * @return {mixed}
     */
    public function eventsFromRange($rangeStart = '01/01/1970', $rangeEnd = '12/31/3000')
    {
        $extendedEvents = array();
        $events = $this->sortEventsWithOrder($this->events(), SORT_ASC);

        if ($events) {
	        $dateStart = new DateTime($rangeStart);
	        $dateEnd   = new DateTime($rangeEnd);

	        $rangeStart = $dateStart->format('U');
	        $rangeEnd   = $dateEnd->format('U');

	        // loop through all events by adding two new elements
	        foreach ($events as $anEvent) {
	            $timestamp = $this->iCalDateToUnixTimestamp($anEvent['DTSTART']);
	            if ($timestamp >= $rangeStart && $timestamp <= $rangeEnd) {
	                $extendedEvents[] = $anEvent;
	            }
	        }
        }

        return $extendedEvents;
    }

    /**
     * Returns a boolean value whether thr current calendar has events or not
     *
     * @param {array} $events    An array with events.
     * @param {array} $sortOrder Either SORT_ASC, SORT_DESC, SORT_REGULAR,
     *                           SORT_NUMERIC, SORT_STRING
     *
     * @return {boolean}
     */
    public function sortEventsWithOrder($events, $sortOrder = SORT_ASC)
    {
        $extendedEvents = [];
        $timestamp = [];

        // loop through all events by adding two new elements
        foreach ($events as $anEvent) {
            if (!array_key_exists('UNIX_TIMESTAMP', $anEvent)) {
                $anEvent['UNIX_TIMESTAMP'] =
                            $this->iCalDateToUnixTimestamp($anEvent['DTSTART']);
            }

            if (!array_key_exists('REAL_DATETIME', $anEvent)) {
                $anEvent['REAL_DATETIME'] =
                            date("d.m.Y", $anEvent['UNIX_TIMESTAMP']);
            }

            $extendedEvents[] = $anEvent;
        }

        foreach ($extendedEvents as $key => $value) {
            $timestamp[$key] = $value['UNIX_TIMESTAMP'];
        }
        
        array_multisort($timestamp, $sortOrder, $extendedEvents);
        return $extendedEvents;
    }
}
