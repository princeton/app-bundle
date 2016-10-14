<?php
/**
 * The RRule class implements an RFC2445 (iCal) RRULE recurrence object.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2016 The Trustees of Princeton University
 */

namespace Princeton\App\CalendarAPI;

use DateTime;
use Exception;

class RRule extends RFC2445
{
    /**
     * @var string
     */
    public $freq;

    /**
     * @var string
     */
    public $interval;

    /**
     * Depending on the FREQ setting, this could be
     * a comma-separated list of day names, e.g. "SU,MO"
     * or of week-day specs, e.g. "1TU" (for "first Tuesday").
     *
     * @var string
     */
    public $byDay;

    /**
     * @var string
     */
    public $count;

    /**
     * @var \DateTime
     */
    public $until;

    /**
     * Formats the object for output.
     *
     * @return string
     */
    public function format()
    {
        $fields = [];

        $keys = ['freq', 'interval', 'byDay', 'count'];

        if (!empty($this->until) && !is_a($this->until, DateTime::class)) {
            throw new Exception("RRule 'until' property must be a DateTime object.");
        }

        foreach ($keys as $key) {
            if (!empty($this->{$key})) {
                $fields[] = strtoupper($key) . '=' . $this->{$key};
            }
        }

        if (empty($this->count) && !empty($this->until)) {
            $fields[] = 'UNTIL=' . $this->dateStr($this->until);
        }

        return join(';', $fields);
    }
}
