<?php

namespace Princeton\App\ExchangeAPI;

interface ExchangeCalDelegate {
    /**
     * Get the Exchange authentication email account.
     *
     * @return string
     */
    public function getEmail();
    
    /**
     * Get the Exchange authentication password.
     *
     * @return string
     */
    public function getPassword();
    
    /**
     * Get the desired Exchange hostname.
     * Only used if auto-discovery fails.
     *
     * @return string
     */
    public function getHostname();
    
    /**
     * Get the Exchange authentication username.
     * Only used if auto-discovery fails.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Get the desired Exchange calendar ID.
     *
     * @return string
     */
    public function getCalendarMailbox();

    /**
     * Event callback for logging warnings from the Exchange service.
     *
     * @param string $message The message to be logged.
     * @return void
     */
    public function logWarning($message);
}