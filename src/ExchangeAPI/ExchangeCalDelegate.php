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
     * Get the Exchange server version.
     * Only used if auto-discovery fails.
     *
     * @return string
     */
    public function getVersion();

    /**
     * Get the desired Exchange calendar ID.
     *
     * @return string
     */
    public function getCalendarMailbox();

    /**
     * Should return a PSR-3 Logger instance, or null if none available.
     *
     * @return Psr\Log\LoggerInterface
     */
    public function getLogger();
}
