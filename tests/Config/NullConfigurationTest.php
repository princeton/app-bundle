<?php

namespace Test\Config;

use Test\TestCase;

/**
 * NullConfiguration test case.
 */
class NullConfigurationTest extends TestCase
{
    /**
     * @var NullConfiguration
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Config\NullConfiguration')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Config\NullConfiguration::config
     * @todo   Implement testConfig().
     */
    public function testConfig()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Config\NullConfiguration::clearCached
     * @todo   Implement testClearCached().
     */
    public function testClearCached()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
