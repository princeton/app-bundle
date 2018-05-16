<?php

namespace Princeton\App\Slim\Middleware;

use ErrorException as PHPErrorException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Princeton\App\Injection\Injectable;

/**
 * A simple Slim middleware object which turns PHP errors into exceptions.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */
class ErrorException implements Injectable
{
    public function __invoke(ServerRequestInterface $req,  ResponseInterface $res, callable $next)
    {
        $errHandler = function ($errno, $errstr, $errfile, $errline) {
            if ($errno & error_reporting()) {
                throw new PHPErrorException($errstr, 0, $errno, $errfile, $errline);
            }

            return false;
        };

        set_error_handler($errHandler);

        return $next($req, $res);
    }
}
