<?php

namespace Princeton\App\GoogleAPI;

/**
 * API for a generic Calendar Event delegate.
 *
 * The delegate provides data from the application for use
 * by a calendar API.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */
interface EventDelegate
{
    /**
     * Get the delegate's object identifier (the ID of the application object
     * which represents the event in question.)
     *
     * @return string
     */
    public function getId();
    
    /**
     * Get the application data which should be used as this event's summary.
     *
     * @return string
     */
    public function getSummary();
    
    /**
     * Get the application data which should be used as this event's description.
     *
     * @return string
     */
    public function getDescription();
    
    /**
     * Get the application data which should be used as this event's location.
     *
     * @return string
     */
    public function getLocation();
    
    /**
     * Get the application data which should be used as this event's start date/time.
     *
     * @return \DateTime
     */
    public function getStartDateTime();
    
    /**
     * Get the application data which should be used as this event's end date/time.
     *
     * @return \DateTime
     */
    public function getEndDateTime();
    
    /**
     * Get the value which should be used as this event's reminder interval.
     *
     * False means "no reminders"; 0 means "the default for this calendar";
     * a number > 0 means that many minutes before the event.
     *
     * @return int
     */
    public function getReminderMinutes();
    
    /**
     * Get the application data which should be used as this event's
     * list of attendee email addresses, if any.
     *
     * @return array Array of strings
     */
    public function getAttendeeEmails();
}
