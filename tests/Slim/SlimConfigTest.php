<?php

namespace Test\Slim;

use Test\TestCase;

/**
 * SlimConfig test case.
 */
class SlimConfigTest extends TestCase
{
    /**
     * @var SlimConfig
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Slim\SlimConfig')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Slim\SlimConfig::create
     * @todo   Implement testCreate().
     */
    public function testCreate()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
