<?php
/**
 * The RRule class implements an RFC2445 (iCal) RRULE recurrence object.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2016 The Trustees of Princeton University
 */

namespace Princeton\App\CalendarAPI;

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
    public function __toString()
    {
        $fields = [];
        
        $keys = ['freq', 'interval', 'byDay', 'count'];
        
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
