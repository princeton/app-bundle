<?php

namespace Princeton\App\GoogleAPI;

use Exception;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Google_Service_Calendar_EventAttendee;

/**
 * This class implements a simple delegate-based interface
 * for dealing with the Google Calendar API.

 * Visit https://code.google.com/apis/console?api=calendar to generate your
 * client id, client secret, and to register your redirect uri.
 *
 * You then need to provide application-specific implementations of the
 * GoogleCalDelegate and GoogleEventDelegate interfaces. These provide
 * the various data and event callbacks which GoogleCal will use to do its work.
 *
 * Google calendar doc:
 * https://developers.google.com/google-apps/calendar/concepts
 *
 * @author Kevin Perry, perry@princeton.edu
 * @author Serge J. Goldstein, serge@princeton.edu
 * @author Kelly D. Cole, kellyc@princeton.edu
 * @copyright 2015 The Trustees of Princeton University
 */
class GoogleCal
{
    /**
     * @var \GoogleCalDelegate
     */
    private $calDelegate;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \GoogleCalDelegate $calDelegate
     */
    public function __construct(GoogleCalDelegate $calDelegate)
    {
        $this->calDelegate = $calDelegate;
        $this->logger = $calDelegate->getLogger();
    }

    /**
     * Obtain google authorization credentials for a google calendar.
     *
     * This method is the core logic that needs to be called by an
     * application's oath2 callback URL.  We test to see what request parameters
     * Google has passed us, to determine where we are in the process.
     * If this doesn't look like a response redirect from Google OAuth,
     * then we redirect to Google OAuth to start the process.  If it looks
     * like the OAuth request was denied for any reason, we call our
     * GoogleCalDelegate's rejected() method.  If we have what looks like
     * an acceptable response, we create a Google_Client, get our
     * GoogleCalDelegate's access token and calendar ID,
     * validate the credentials for that calendar, and call the delegate's
     * approved() method.
     *
     * @return mixed Application-specific: it returns the value that is returned
     *         by the called GoogleCalDelegate event callback
     *         (either rejected(), approved(), or redirect()).
     */
    public function authorize()
    {
        $result = false;
        if (isset($_REQUEST['logout']) || isset($_REQUEST['error'])) {
            // The access request has been rejected.
            $this->calDelegate->setGoogleToken(null);
            $this->calDelegate->setGoogleCalendarId(null);
            $result = $this->calDelegate->rejected();
        } elseif (isset($_REQUEST['code'])) {
            // The access request has been approved.
            /* @var $client \Google_Client */
            $client = $this->buildClient();
            $gcal = new Google_Service_Calendar($client);
            $client->authenticate($_REQUEST['code']);
            $this->calDelegate->setGoogleToken($client->getAccessToken());
            $this->calDelegate->setGoogleCalendarId($this->getCalList($gcal));
            $result = $this->calDelegate->approved();
        } else {
            // First time through - redirect to the OAuth access manager.
            /* @var $client \Google_Client */
            $client = $this->buildClient();
            new Google_Service_Calendar($client);
            $result = $this->calDelegate->redirect($client->createAuthUrl());
        }
        return $result;
    }

    /**
     * This method just checks whether we still have permission to edit
     * the given calendar.  We create a Google_Client, get our GoogleCalDelegate's
     * access token and calendar ID, and validate the credentials
     * for that calendar.
     *
     * @return \Google_Auth_LoginTicket or false on failure
     */
    public function checkToken()
    {
        try {
            if ($this->isConfigured()) {
                $token = $this->calDelegate->getGoogleToken();
                return $this->buildClient()->verifyIdToken($token);
            }
        } catch (Exception $ex) {
            // ignore - we're returning false anyway.
        }
        return false;
    }
    
    /**
     * Insert an event into a Google calendar.
     *
     * If we have a valid calendar ID and token from our delegate,
     * then we build a Google_Client, create an event containing
     * data from the GoogleEventDelegate, and insert it on the
     * appropriate calendar.
     *
     * @param \GoogleEventDelegate $eventDelegate
     *            the delegate for the event to be created.
     * @return string The Google event id
     */
    public function insertEvent(GoogleEventDelegate $eventDelegate)
    {
        $status = false;
        $calId = 0;

        try {
            if ($this->isConfigured()) {
                $client = $this->buildClient();
                $gcal = new Google_Service_Calendar($client);
                
                $this->configureToken($client);

                $calId = $this->calDelegate->getGoogleCalendarId();
                $event = $this->createEvent($eventDelegate);
                $opts = $this->calDelegate->getOptions();
    
                $createdEvent = $gcal->events->insert($calId, $event, $opts);
                $status = $createdEvent->getId();
                if ($status) {
                    $eventDelegate->setGoogleId($status);
                }
            }
        } catch (Exception $ex) {
            $this->logWarning(
                "Google sync error inserting event for item "
                . $eventDelegate->getId()
                . " on calendar $calId: "
                . $ex->getMessage()
            );
        }
        
        return $status;
    }

    /**
     * Update an event in a Google calendar.
     *
     * If we have a valid calendar ID and token from our delegate,
     * then we build a Google_Client, create an event containing
     * data from the GoogleEventDelegate, and update the
     * appropriate calendar event item from the event data.
     *
     * @param \GoogleEventDelegate $eventDelegate
     *            the delegate for the event to be updated.
     * @return bool True on success
     */
    public function updateEvent(GoogleEventDelegate $eventDelegate)
    {
        $status = false;

        try {
            $gid = $eventDelegate->getGoogleId();
            if ($this->isConfigured() && isset($gid)) {
                $client = $this->buildClient();
                $gcal = new Google_Service_Calendar($client);
                 
                $this->configureToken($client);
        
                $calId = $this->calDelegate->getGoogleCalendarId();
                $event = $this->createEvent($eventDelegate);

                $event = $gcal->events->update($calId, $gid, $event);
                $status = true;
            }
        } catch (Exception $ex) {
            $this->logWarning(
                "Google sync error updating event for item "
                . $eventDelegate->getId()
                . " on calendar $calId: "
                . $ex->getMessage()
            );
        }

        return $status;
    }

    /**
     * Delete an event from a Google calendar.
     *
     * If we have a valid calendar ID and token from our delegate,
     * then we build a Google_Client, and delete the
     * appropriate calendar event item.
     *
     * @param \GoogleEventDelegate $eventDelegate
     *            the delegate for the event to be deleted.
     * @return bool True on success
     */
    public function deleteEvent(GoogleEventDelegate $eventDelegate)
    {
        $status = false;

        try {
            $gid = $eventDelegate->getGoogleId();
            if ($this->isConfigured() && isset($gid)) {
                $client = $this->buildClient();
                $gcal = new Google_Service_Calendar($client);
                 
                $this->configureToken($client);
        
                $calId = $this->calDelegate->getGoogleCalendarId();

                $status = $gcal->events->delete($calId, $gid);
                $eventDelegate->setGoogleId(null);
                $status = true;
            }
        } catch (Exception $ex) {
            $this->logWarning(
                "Google sync error deleting event for item "
                . $eventDelegate->getId()
                . " on calendar $calId: "
                . $ex->getMessage()
            );
        }
        
        return $status;
    }

    /**
     *  We must have both a calendar id and an access token
     *  in order to be properly configured.
     */
    protected function isConfigured()
    {
        $calId = $this->calDelegate->getGoogleCalendarId();
        $token = $this->calDelegate->getGoogleToken();
        return ($calId && $token);
    }
    
    /**
     * Create a Google client object.
     *
     * @return \Google_Client
     */
    protected function buildClient()
    {
        /* Set up the google calendar objects */
        $client = new Google_Client();

        if ($this->logger) {
            $client->setLogger($this->logger);
        }

        $client->setAuthConfig($this->calDelegate->getAuthConfig());
        $client->setRedirectUri($this->calDelegate->getRedirectUri());
        $client->setAccessType('offline');
        $client->addScope(Google_Service_Calendar::CALENDAR);

//         if (isset($impersonated)) {
//             $client->setSubject($impersonated);
//         }
        
        return $client;
    }
    
    /**
     * Set up the authentication token; if expired, refresh it.
     *
     * @param \Google_Client $client
     * @return void
     */
    protected function configureToken(Google_Client $client)
    {
        $token = $this->calDelegate->getGoogleToken();
        $client->setAccessToken($token);
        
        if ($client->isAccessTokenExpired()) {
            $refreshToken = json_decode($token)->refresh_token;
            $client->refreshToken($refreshToken);
            $this->calDelegate->setGoogleToken($client->getAccessToken());
        }
    }

    /**
     * Get the list of calendar names defined for the given calendar object.
     *
     * @param \Google_Service_Calendar $cal
     * @return array
     */
    protected function getCalList(Google_Service_Calendar $cal)
    {
        // Get list of owned calendars.
        $calList = $cal->calendarList->listCalendarList();
        $ownCals = array();
        foreach ($calList['items'] as $item) {
            if (trim($item['accessRole']) == 'owner') {
                $ownCals[] = $item['id'];
            }
        }
        // For now, just use the primary calendar.
        $ownCals = array('primary');
        
        return $ownCals;
    }
    
    /**
     * Create a Google Calendar Event.
     *
     * @param \GoogleEventDelegate $eventDelegate
     *            the delegate for the event to be created.
     * @return \Google_Service_Calendar_Event
     */
    protected function createEvent(GoogleEventDelegate $eventDelegate)
    {
        $event = new Google_Service_Calendar_Event();
        
        $event->setSummary($eventDelegate->getSummary());
        $event->setDescription($eventDelegate->getDescription());
        $event->setLocation($eventDelegate->getLocation());
        
        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime($eventDelegate->getStartDateTime()->format(DATE_ISO8601));
        $start->setTimeZone($eventDelegate->getTimeZone());
        $event->setStart($start);
        
        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime($eventDelegate->getEndDateTime()->format(DATE_ISO8601));
        $end->setTimeZone($eventDelegate->getTimeZone());
        $event->setEnd($end);

        $event->setRecurrence($eventDelegate->getGoogleRecurrence());
        
        $attendees = array();
        foreach ($eventDelegate->getAttendeeEmails() as $email) {
            $attendee = new Google_Service_Calendar_EventAttendee();
            $attendee->setEmail($email);
            $attendees[] = $attendee;
        }
        $event->setAttendees($attendees);
        
        return $event;
    }
    
    protected function logWarning($message)
    {
        if ($this->logger) {
            $this->logger->warning($message);
        }
    }
}
