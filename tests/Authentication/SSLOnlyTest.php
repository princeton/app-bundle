<?php

namespace Test\Authentication;

use Test\TestCase;

/**
 * SSLOnly test case.
 */
class SSLOnlyTest extends TestCase
{
    /**
     * @var SSLOnly
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Authentication\SSLOnly')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    public function testNothing()
    {
        $this->assertTrue(true);
    }
}
