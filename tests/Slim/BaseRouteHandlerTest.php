<?php

namespace Test\Slim;

use Test\TestCase;
use Princeton\App\Slim\BaseRouteHandler;

/**
 * BaseRouteHandler test case.
 */
class BaseRouteHandlerTest extends TestCase
{
    /**
     * @var BaseRouteHandler
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        $this->object = new BaseRouteHandler();
    }

    /**
     * @covers Princeton\App\Slim\BaseRouteHandler::get
     * @todo   Implement testGet().
     */
    public function testGet()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Slim\BaseRouteHandler::post
     * @todo   Implement testPost().
     */
    public function testPost()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Slim\BaseRouteHandler::put
     * @todo   Implement testPut().
     */
    public function testPut()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Slim\BaseRouteHandler::patch
     * @todo   Implement testPatch().
     */
    public function testPatch()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Slim\BaseRouteHandler::delete
     * @todo   Implement testDelete().
     */
    public function testDelete()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Slim\BaseRouteHandler::options
     * @todo   Implement testOptions().
     */
    public function testOptions()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
