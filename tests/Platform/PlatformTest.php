<?php

namespace Test\Platform;

use Test\TestCase;

/**
 * Platform test case.
 */
class PlatformTest extends TestCase
{
    /**
     * @var Platform
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Platform\Platform')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    /**
     * @covers Princeton\App\Platform\Platform::getServices
     * @todo   Implement testGetServices().
     */
    public function testGetServices()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
