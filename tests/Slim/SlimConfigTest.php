<?php

namespace Test\Slim;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Environment;
use Test\TestCase;
use Princeton\App\Injection\Injectable;
use Princeton\App\Slim\SlimConfig;
use Princeton\App\Slim\BaseRouteHandler;

class TestRoute extends BaseRouteHandler implements Injectable
{
    public static $ran = false;

    public function get($id = null)
    {
        self::$ran = true;
        return $this->response;
    }
}

class MiddlewareA implements Injectable
{
    public static $ran = false;

    public function __invoke(ServerRequestInterface $req,  ResponseInterface $res, callable $next)
    {
        if (self::$ran) {
            throw new Exception('Running MWA twice!');
        }

        self::$ran = true;
        return $next($req, $res);
    }
}

class MiddlewareB implements Injectable
{
    public static $ran = false;

    public function __invoke(ServerRequestInterface $req,  ResponseInterface $res, callable $next)
    {
        if (self::$ran) {
            throw new Exception('Running MWB twice!');
        }

        if (MiddlewareA::$ran) {
            self::$ran = true;
        }

        return $next($req, $res);
    }
}

class Middleware1 implements Injectable
{
    public static $ran = false;

    public function __invoke(ServerRequestInterface $req,  ResponseInterface $res, callable $next)
    {
        if (self::$ran) {
            throw new Exception('Running MW1 twice!');
        }

        if (MiddlewareB::$ran) {
            self::$ran = true;
        }

        return $next($req, $res);
    }
}

class Middleware2 implements Injectable
{
    public static $ran = false;

    public function __invoke(ServerRequestInterface $req,  ResponseInterface $res, callable $next)
    {
        if (self::$ran) {
            throw new Exception('Running MW2 twice!');
        }

        if (Middleware1::$ran) {
            self::$ran = true;
        }

        return $next($req, $res);
    }
}

/**
 * SlimConfig test case.
 */
class SlimConfigTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        TestRoute::$ran = false;
        MiddlewareA::$ran = false;
        MiddlewareB::$ran = false;
        Middleware1::$ran = false;
        Middleware2::$ran = false;
    }

    /**
     * @covers Princeton\App\Slim\SlimConfig::build
     */
    public function testBuild()
    {
        $subject = new SlimConfig();
        $app = $subject->build($this->getTestConfig('/test'));
        $this->assertSame('/test', $app->getContainer()->get('router')->pathFor('Test Route'));
        $app->run();
        $this->assertTrue(MiddlewareA::$ran);
        $this->assertTrue(MiddlewareB::$ran);
        $this->assertTrue(Middleware1::$ran);
        $this->assertTrue(Middleware2::$ran);
        $this->assertTrue(TestRoute::$ran);
    }

    /**
     * @covers Princeton\App\Slim\SlimConfig::build
     */
    public function testBuildGroup()
    {
        $subject = new SlimConfig();
        $app = $subject->build($this->getGroupConfig());

        $this->assertSame('/group/test', $app->getContainer()->get('router')->pathFor('Group Route'));
        $app->run();
        $this->assertTrue(MiddlewareA::$ran);
        $this->assertTrue(MiddlewareB::$ran);
        $this->assertTrue(Middleware1::$ran);
        $this->assertTrue(Middleware2::$ran);
        $this->assertTrue(TestRoute::$ran);

        $this->expectException(\RuntimeException::class);
        $app->getContainer()->get('router')->pathFor('Non-matching Route');
    }

    /**
     * @covers Princeton\App\Slim\SlimConfig::create
     * @todo   Implement testCreate().
     */
    public function testCreate()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    protected function getTestConfig($path)
    {
        return [
            'config' => [
                'container' => [
                    'settings' => [
                        'displayErrorDetails' => true,
                    ],
                    'environment' => Environment::mock([
                        'REQUEST_METHOD' => 'GET',
                        'REQUEST_URI' => $path,
                        'QUERY_STRING' => '',
                        'SERVER_NAME' => 'example.com',
                        'CONTENT_TYPE' => 'application/json;charset=utf8',
                        'CONTENT_LENGTH' => 0,
                    ]),
                ],
                'injections' => [],
            ],
            'middleware' => [
                MiddlewareA::class,
                MiddlewareB::class,
            ],
            'routes' => [
                '/test' => [
                    'name' => 'Test Route',
                    'handler' => TestRoute::class,
                    'middleware' => [
                        Middleware1::class,
                        Middleware2::class,
                    ],
                ],
            ],
        ];
    }

    protected function getGroupConfig()
    {
        $config = $this->getTestConfig('/group/test');
        $config['routeGroups'] = [
            '/group' => [
                'routes' => [
                    '/test' => [
                        'name' => 'Group Route',
                        'handler' => TestRoute::class,
                        'middleware' => [
                            Middleware1::class,
                            Middleware2::class,
                        ],
                    ],
                ],
            ],
            '/group2' => [
                'routes' => [
                    '/test' => [
                        'name' => 'Non-matching Route',
                        'handler' => TestRoute::class,
                        'middleware' => [
                            Middleware1::class,
                            Middleware2::class,
                        ],
                    ],
                ],
            ]
        ];

        return $config;
    }
}
