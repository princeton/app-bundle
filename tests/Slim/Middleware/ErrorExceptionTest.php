<?php

namespace Test\Slim\Middleware;

use Test\TestCase;

/**
 * ErrorException test case.
 */
class ErrorExceptionTest extends TestCase
{
    /**
     * @var ErrorException
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Slim\Middleware\ErrorException')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Slim\Middleware\ErrorException::call
     * @todo   Implement testCall().
     */
    public function testCall()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
