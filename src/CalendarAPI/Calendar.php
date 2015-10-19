<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University
 */

namespace Princeton\App\CalendarAPI;

/**
 * API for a generic Calendar API.
 */
class Calendar {
    /**
     * Insert an event into a calendar.
     *
     * @param EventDelegate $eventDelegate
     *            the delegate for the event to be created.
     * @return bool True on success.
     */
    public function insertEvent(EventDelegate $eventDelegate);

    /**
     * Update an event in a calendar.
     *
     * @param EventDelegate $eventDelegate
     *            the delegate for the event to be updated.
     * @return bool True on success
     */
    public function updateEvent(EventDelegate $eventDelegate);

    /**
     * Delete an event from a calendar.
     *
     * @param EventDelegate $eventDelegate
     *            the delegate for the event to be deleted.
     * @return bool True on success
     */
    public function deleteEvent(EventDelegate $eventDelegate);
}