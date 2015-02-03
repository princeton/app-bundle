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
interface GoogleEventDelegate
{
	/**
	 * Get the ID of the event that this delegate has been associated with.
     * @return string
	 */
    public function getGoogleId();
    
    /**
     * Set the event ID with which this delegate is associated.
     * @param unknown $gid
     * @return void
     */
    public function setGoogleId($gid);

    /**
     * Get the delegate's object identifier (the ID of the application object
     * which represents the Google event in question.)
     * @return string
     */
    public function getId();
    
    /**
     * Get the application data which should be used as this event's summary.
     * @return string
     */
    public function getSummary();
    
    /**
     * Get the application data which should be used as this event's description.
     * @return string
     */
    public function getDescription();
    
    /**
     * Get the application data which should be used as this event's location.
     * @return string
     */
    public function getLocation();
    
    /**
     * Get the application data which should be used as this event's start date/time.
     * @return \DateTime
     */
    public function getStartDateTime();
    
    /**
     * Get the application data which should be used as this event's end date/time.
     * @return \DateTime
     */
    public function getEndDateTime();
    
    /**
     * Get the application data which should be used as this event's attendee emails.
     * @return array of strings.
     */
    public function getAttendeeEmails();
}

