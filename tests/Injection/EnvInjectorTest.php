<?php

namespace Test\Injection;

use Test\TestCase;

/**
 * EnvInjector test case.
 */
class EnvInjectorTest extends TestCase
{
    /**
     * @var EnvInjector
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Injection\EnvInjector')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    public function testNothing()
    {
        $this->assertTrue(true);
    }
}
