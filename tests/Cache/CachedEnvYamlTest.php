<?php

namespace Test\Cache;

use Test\TestCase;
use Princeton\App\Cache\CachedEnvYaml;

/**
 * CachedEnvYaml test case.
 */
class CachedEnvYamlTest extends TestCase
{
    /**
     * @var CachedEnvYaml
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();

        //$mock = $this->getMockBuilder(CachedEnvYaml::class)
            //->setConstructorArgs([])->setMethods(null)->getMock();

        //$this->object = $mock;
    }

    public function testNothing()
    {
        $this->assertTrue(true);
    }
}
