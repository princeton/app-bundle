<?php

namespace Princeton\App\Authentication;

/**
 * A delegate API for the RememberMe authenticator.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */
interface RememberMeDelegate
{
    /**
     * Get the currently valid token, if any, for the given device,
     * from the application.
     *
     * @param string $username The user's name.
     * @param string $device The device ID.
     * @return string The stored token, or null if there is none.
     */
    public function getToken($username, $device);

    /**
     * Tell the application to store the currently valid token for the given device.
     *
     * @param string $username The user's name.
     * @param string $device The device ID.
     * @param string $token The new token to save.
     * @return void
     */
    public function setToken($username, $device, $token);
}
