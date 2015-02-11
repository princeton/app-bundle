<?php

namespace Princeton\App\GoogleAPI;

/**
 * API for a Google Calendar Event delegate.
 *
 * The delegate provides data from the application for use
 * by the Google Calendar Event API.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */
interface GoogleEventDelegate extends EventDelegate
{
    /**
     * Get the ID of the event that this delegate has been associated with.
     *
     * @return string
     */
    public function getGoogleId();
    
    /**
     * Set the event ID with which this delegate is associated.
     *
     * @param unknown $gid
     * @return void
     */
    public function setGoogleId($gid);
    
    /**
     * Get the value which should be used as this event's recurrence specification.
     *
     * An array of RFC 2445 recurrence rules.
     *
     * @see http://www.ietf.org/rfc/rfc2445
     * @return array
     */
    public function getGoogleRecurrence();
}

