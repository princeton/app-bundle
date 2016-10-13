<?php

namespace Princeton\App\GoogleAPI;

/**
 *
 * API for a Google Calendar delegate.
 *
 * The delegate provides data from the application for use
 * by the Google Calendar API.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */
interface GoogleCalDelegate
{
    /**
     * Get the Google authorization config array
     *
     * @return array
     */
    public function getAuthConfig();
    
    /**
     * Get the application's OAuth2 redirect url
     *
     * @return string
     */
    public function getRedirectUri();

    /**
     * Get the OAuth2 validation token
     *
     * @return string
     */
    public function getGoogleToken();

    /**
     * Store the OAuth2 validation token
     * param $token string
     * return void
     */
    public function setGoogleToken($token);

    /**
     * Get the desired set of Google calendar options.
     *
     * @return string
     */
    public function getOptions();

    /**
     * Get the desired Google calendar ID.
     *
     * @return string
     */
    public function getGoogleCalendarId();

    /**
     * Set the desired Google calendar ID.
     *
     * @param array $list List of available calendar ID's
     * @return void
     */
    public function setGoogleCalendarId($list);

    /**
     * Event callback for a failed authorization.
     *
     * @return mixed Application-specific
     */
    public function rejected();

    /**
     * Event callback for an approved authorization.
     *
     * @return mixed Application-specific
     */
    public function approved();

    /**
     * Event callback for redirecting to the OAuth authenticator.
     *
     * @return mixed Application-specific
     */
    public function redirect($url);

    /**
     * Should return a PSR-3 Logger instance, or null if none available.
     *
     * @return Psr\Log\LoggerInterface
     */
    public function getLogger();
}

