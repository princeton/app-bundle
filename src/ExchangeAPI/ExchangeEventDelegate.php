<?php

namespace Princeton\App\ExchangeAPI;

use Princeton\App\GoogleAPI\EventDelegate;

interface ExchangeEventDelegate extends EventDelegate
{
    /**
     * Get the ID of the event that this delegate has been associated with.
     *
     * @return string
     */
    public function getEwsId();
    
    /**
     * Set the event ID with which this delegate is associated.
     *
     * @param string $eid
     * @return void
     */
    public function setEwsId($eid);
    
    /**
     * Get the change key with which this delegate is associated.
     *
     * @return string
     */
    public function getEwsChangeKey();
    
    /**
     * Set the change key with which this delegate is associated.
     *
     * @param string $gid
     * @return void
     */
    public function setEwsChangeKey($key);

    /**
     * Get the application data which should be used as this event's importance.
     * Allowed values are "High", "Normal", and "Low".
     *
     * @return string
     */
    public function getEwsImportance();
    
    /**
     * Get the values which should be used to define this event's recurrence specification.
     * Should return an array containing values for:
     * (ISO8601 string) startDate, (ISO8601 string) endDate, (string) period, (int) interval,
     * (array of ints) days, and (array of ISO8601 strings) deletions.
     *
     * @return array
     */
    public function getEwsRecurrence();
}
