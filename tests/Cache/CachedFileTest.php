<?php

namespace Test\Cache;

use Test\TestCase;

/**
 * CachedFile test case.
 */
class CachedFileTest extends TestCase
{
    /**
     * @var CachedFile
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Cache\CachedFile')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Cache\CachedFile::fetch
     * @todo   Implement testFetch().
     */
    public function testFetch()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Cache\CachedFile::getCache
     * @todo   Implement testGetCache().
     */
    public function testGetCache()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
