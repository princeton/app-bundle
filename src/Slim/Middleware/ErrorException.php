<?php

namespace Princeton\App\Slim\Middleware;

/**
 * A simple Slim middleware object which turns PHP errors into exceptions.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */
class ErrorException extends \Slim\Middleware
{
    public function call()
    {
        set_error_handler(
            function ($errno, $errstr, $errfile, $errline)
            {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        );

        $this->next->call();

        restore_error_handler();
    }
}
