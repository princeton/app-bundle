<?php

namespace Test\Cache;

use Test\TestCase;

/**
 * CachedYaml test case.
 */
class CachedYamlTest extends TestCase
{
    /**
     * @var CachedYaml
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Cache\CachedYaml')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    public function testNothing()
    {
        $this->assertTrue(true);
    }
}
