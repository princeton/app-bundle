<?php
/**
 * The VEvent class implements an RFC2445 (iCal) VEVENT object.
 *
 * The dtstamp, dtstart and dtend properties should be set to \DateTime values.
 * The other properties are all strings.
 *
 * To get an iCal "VEVENT" output string,
 * you can either cast an instance of this class to (string)
 * or use its format() method.
 *
 * Use the formatStream() method to get a complete iCal calendar stream
 * containing just this one event.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015, 2016 The Trustees of Princeton University
 */

namespace Princeton\App\CalendarAPI;

use DateTime;
use Exception;

class VEvent extends RFC2445
{
    /**
     * @var string
     */
    public $uid;
    
    /**
     * @var \DateTime
     */
    public $dtstamp;
    
    /**
     * @var string
     */
    public $summary;
    
    /**
     * @var \DateTime
     */
    public $dtstart;
    
    /**
     * @var \DateTime
     */
    public $dtend;
    
    /**
     * Priority should be an int between 1 and 9.
     *
     * @var int
     */
    public $priority;
    
    /**
     * @var string
     */
    public $location;
    
    /**
     * @var string
     */
    public $url;
    
    /**
     * @var string
     */
    public $description;
    
    /**
     * Should be an ISO8601 interval string.
     * Example: "-PT15M" sets a reminder 15 mins before event.
     *
     * @var string
     */
    public $reminder;
    
    /**
     * @var string
     */
    public $transp = self::OPAQUE;
    
    /**
     * @var string[]
     */
    public $categories = [];
    
    /**
     * @var DateTime[]
     */
    public $exdate = [];
    
    /**
     * @var RRule
     */
    public $rrule;
    
    /**
     * Formats the object for output as an iCal VEvent stream.
     * @return string
     */
    public function format()
    {
        $lines = ['BEGIN:VEVENT'];

        $dateKeys = ['dtstamp', 'dtstart', 'dtend'];
        $plainKeys = ['uid', 'summary', 'location', 'url', 'description', 'transp'];
        
        $this->validateFields($dateKeys);
        
        foreach ($plainKeys as $key) {
            if (!empty($this->{$key})) {
                $lines[] = strtoupper($key) . ':' . $this->string($this->{$key});
            }
        }
        
        if ($this->priority >= 1 && $this->priority <= 9) {
            $lines[] = 'PRIORITY:' . (int)$this->priority;
        }

        if (!empty($this->categories)) {
            $lines[] = 'CATEGORIES:' . join(',', array_map([$this, 'string'], $this->categories));
        }
        
        foreach ($dateKeys as $key) {
            if (!empty($this->{$key})) {
                $lines[] = strtoupper($key) . ':' . $this->dateStr($this->{$key});
            }
        }
        
        if ($this->rrule) {
            $lines[] = 'RRULE:' . (string)$this->rrule;
        }

        if ($this->rrule && !empty($this->exdate)) {
            $lines[] = 'EXDATE:' . join(',', array_map([$this, 'dateStr'], $this->exdate));
        }
        
        if (!empty($this->reminder)) {
            $lines = array_merge($lines, $this->reminderAlarm($this->reminder));
        }

        $lines[] = 'END:VEVENT';
        
        return $this->assemble($lines);
    }

    /**
     * Formats the object for output as an iCal Calendar stream.
     *
     * @param string $name
     * @param string $prodId
     * @param \DateTime $start
     * @param string $version
     * @return string
     */
    public function formatStream($name, $prodId, DateTime $start, $version = '1.0')
    {
        $vcal = new VCalendar($name, $prodId, $start, $version);
        $vcal->addEvent($this);

        return $vcal->format();
    }
    
    /**
     * Validate all required and object-valued fields.
     *
     * @param string[] $dateKeys The list of keys that should be DateTime objects
     * @throws Exception
     */
    protected function validateFields($dateKeys)
    {
        $reqKeys = ['uid', 'dtstamp', 'dtstart'];
        
        foreach ($reqKeys as $key) {
            if (empty($this->{$key})) {
                throw new Exception("VEvent '$key' property is required.");
            }
        }

        foreach ($dateKeys as $key) {
            if (!empty($this->{$key}) && get_class($this->{$key}) !== DateTime::class) {
                throw new Exception("VEvent '$key' property must be a DateTime object.");
            }
        }

        if (!empty($this->rrule) && get_class($this->rrule) !== RRule::class) {
            throw new Exception("VEvent 'rrule' property must be an RRule object.");
        }
    }
    
    /**
     * @param string $interval An ISO8601 interval, e.g. "-PT15M" sets a reminder 15 mins before event.
     * @return string
     */
    protected function reminderAlarm($interval)
    {
        return ($interval ? [
                'BEGIN:VALARM',
                "TRIGGER:$interval",
                'ACTION:DISPLAY',
                'DESCRIPTION:Reminder',
                'END:VALARM',
            ] : []);
    }
}
