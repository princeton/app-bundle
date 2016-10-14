<?php
/**
 * The VCalendar class implements an RFC2445 (iCal) VCALENDAR object.
 *
 * The calstart property should be set to a \DateTime value.
 * The other properties are all strings.
 *
 * To get an iCal "VCALENDAR" output string,
 * you can either cast an instance of this class to (string)
 * or use its format() method.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2016 The Trustees of Princeton University
 */

namespace Princeton\App\CalendarAPI;

use DateTime;
use Exception;

class VCalendar extends RFC2445
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $prodId;

    /**
     * @var \DateTime
     */
    public $start;

    /**
     * @var array
     */
    protected $events = [];

    /**
     * @param string $name
     * @param string $prodId
     * @param DateTime $start
     */
    public function __construct($name = '', $prodId = '', DateTime $start = null)
    {
        parent::__construct();

        $this->name = $name;
        $this->prodId = $prodId;
        $this->start = $start;
    }

    /**
     * Formats the object for output.
     *
     * @return string
     */
    public function format()
    {
        if (!empty($this->start) && !is_a($this->start, DateTime::class)) {
            throw new Exception("VCalendar 'start' property must be a DateTime object.");
        }
        
        return $this->beginVCal() . join('', $this->events) . $this->endVCal();
    }

    /**
     * Add an event to the calendar
     *
     * @param VEvent
     */
    public function addEvent(VEvent $event)
    {
        $this->events[] = $event;
    }

    /**
     * Formats the head of an iCal calendar stream.
     *
     * @return string
     */
    protected function beginVCal()
    {
        return $this->assemble([
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'METHOD:PUBLISH',
            'PRODID:' . $this->string($this->prodId),
            'X-CALSTART:' . $this->dateStr($this->start),
            'X-WR-CALNAME:' . $this->string($this->name),
        ]);
    }

    /**
     * Formats the end of an iCal calendar stream.
     *
     * @return string
     */
    protected function endVCal()
    {
        return $this->assemble(['END:VCALENDAR']);
    }
}
