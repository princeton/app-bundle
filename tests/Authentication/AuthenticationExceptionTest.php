<?php

namespace Test\Authentication;

use Test\TestCase;

/**
 * AuthenticationException test case.
 */
class AuthenticationExceptionTest extends TestCase
{
    /**
     * @var AuthenticationException
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Authentication\AuthenticationException')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    public function testNothing()
    {
        $this->assertTrue(true);
    }
}
