<?php

namespace Test\Injection;

use Test\TestCase;

/**
 * StandardDependencyManager test case.
 */
class StandardDependencyManagerTest extends TestCase
{
    /**
     * @var StandardDependencyManager
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Injection\StandardDependencyManager')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    public function testNothing()
    {
    }
}
