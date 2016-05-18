<?php

namespace Test;

use Princeton\App\Injection\StandardDependencyManager;

/**
 * Standard TestCase causes re-initialization of dependency management.
 * All test cases should inherit from this class.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2015 The Trustees of Princeton University.
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        // Fake that we came from a Web request.
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['SCRIPT_NAME'] = '/';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 443;
        $_SERVER['HTTPS'] = true;
    }
    
    protected function setUp()
    {
        StandardDependencyManager::register(true);
    }
}
