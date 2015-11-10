<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */

namespace Princeton\App\GoogleAPI;

/**
 * Backward-compatibility API - use CalendarAPI\EventDelegate instead.
 *
 * @see Princeton\App\CalendarAPI\EventDelegate
 */
interface EventDelegate extends \Princeton\App\CalendarAPI\EventDelegate
{
    public function getTimeZone();
}