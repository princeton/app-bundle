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

class Middleware implements Injectable
{
    protected $name = 0;
    public static $ran = 0;
    public static $ran0 = false;

    public function __invoke(ServerRequestInterface $req,  ResponseInterface $res, callable $next)
    {
        if (static::${"ran$this->name"}) {
            throw new Exception("Running MW $this->name twice!");
        }

        if (self::$ran === $this->name - 1) {
            static::${"ran$this->name"} = true;
            self::$ran = $this->name;
        }

        return $next($req, $res);
    }
}

class Middleware1 extends Middleware {
    protected $name = 1;
    public static $ran1 = false;
}

class Middleware2 extends Middleware {
    protected $name = 2;
    public static $ran2 = false;
}

class Middleware3 extends Middleware {
    protected $name = 3;
    public static $ran3 = false;
}

class Middleware4 extends Middleware {
    protected $name = 4;
    public static $ran4 = false;
}

class Middleware5 extends Middleware {
    protected $name = 5;
    public static $ran5 = false;
}

class Middleware6 extends Middleware {
    protected $name = 6;
    public static $ran6 = false;
}

class Middleware3b extends Middleware {
    protected $name = 3;
    public static $ran3 = false;
}

class Middleware4b extends Middleware {
    protected $name = 4;
    public static $ran4 = false;
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
        Middleware::$ran = 0;
        Middleware1::$ran1 = false;
        Middleware2::$ran2 = false;
        Middleware3::$ran3 = false;
        Middleware4::$ran4 = false;
        Middleware5::$ran5 = false;
        Middleware6::$ran6 = false;
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
        $this->assertTrue(Middleware1::$ran1);
        $this->assertTrue(Middleware2::$ran2);
        $this->assertTrue(Middleware3::$ran3);
        $this->assertTrue(Middleware4::$ran4);
        $this->assertTrue(TestRoute::$ran);
        $this->assertFalse(Middleware5::$ran5);
        $this->assertFalse(Middleware3b::$ran3);
        $this->assertFalse(Middleware4b::$ran4);
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
        $this->assertTrue(Middleware1::$ran1);
        $this->assertTrue(Middleware2::$ran2);
        $this->assertTrue(Middleware3::$ran3);
        $this->assertTrue(Middleware4::$ran4);
        $this->assertTrue(Middleware5::$ran5);
        $this->assertTrue(Middleware6::$ran6);
        $this->assertTrue(TestRoute::$ran);
        $this->assertFalse(Middleware3b::$ran3);
        $this->assertFalse(Middleware4b::$ran4);

        $this->expectException(\RuntimeException::class);
        $app->getContainer()->get('router')->pathFor('Non-matching Route');
    }

    /**
     * @covers Princeton\App\Slim\SlimConfig::create
     */
    public function testCreate()
    {
        $f = tempnam(sys_get_temp_dir(), '');
        file_put_contents($f, json_encode($this->getTestConfig('/test')));
        $subject = new SlimConfig();
        $app = $subject->create($f);
        unlink($f);
        $this->assertSame('/test', $app->getContainer()->get('router')->pathFor('Test Route'));
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
                Middleware1::class,
                Middleware2::class,
            ],
            'routes' => [
                '/test' => [
                    'name' => 'Test Route',
                    'handler' => TestRoute::class,
                    'middleware' => [
                        Middleware3::class,
                        Middleware4::class,
                    ],
                ],
            ],
        ];
    }

    protected function getGroupConfig()
    {
        $config = $this->getTestConfig('/group/test');
        $config['routes'] = [];
        $config['routeGroups'] = [
            '/group' => [
                'middleware' => [
                    Middleware3::class,
                    Middleware4::class,
                ],
                'routes' => [
                    '/test' => [
                        'name' => 'Group Route',
                        'handler' => TestRoute::class,
                        'middleware' => [
                            Middleware5::class,
                            Middleware6::class,
                        ],
                    ],
                ],
            ],
            '/group2' => [
                'middleware' => [
                    Middleware3b::class,
                    Middleware4b::class,
                ],
                'routes' => [
                    '/test' => [
                        'name' => 'Non-matching Route',
                        'handler' => TestRoute::class,
                        'middleware' => [
                            Middleware3b::class,
                            Middleware4b::class,
                        ],
                    ],
                ],
            ]
        ];

        return $config;
    }
}
