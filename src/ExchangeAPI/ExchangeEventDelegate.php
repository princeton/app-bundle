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
}
