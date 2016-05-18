<?php

namespace Test\Exceptions;

use Test\TestCase;

/**
 * ApplicationException test case.
 */
class ApplicationExceptionTest extends TestCase
{
    /**
     * @var ApplicationException
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Exceptions\ApplicationException')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
