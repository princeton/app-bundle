<?php

namespace Test\Exceptions;

use Test\TestCase;

/**
 * DependencyException test case.
 */
class DependencyExceptionTest extends TestCase
{
    /**
     * @var DependencyException
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Exceptions\DependencyException')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
