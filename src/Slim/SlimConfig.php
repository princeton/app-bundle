<?php

namespace Princeton\App\Slim;

use InvalidArgumentException;
use Slim\App;
use Slim\Route;
use Slim\Views\Twig;
use Symfony\Component\Yaml\Exception\ParseException;
use Princeton\App\Cache\CachedEnvYaml;

/**
 * Implements a YAML-based route configurator for Slim.
 *
 * Supports the following structure:
 *<pre>
 * config:
 *   view:  \namespace\viewClass1
 *   settings: [
 *       autowire: true,
 *       singletonReflection: true,
 *       // ... Slim config settings.
 *   ]
 * middleware: [ \namespace\mwClass1, \namespace\mwClass2 ]
 * routes:
 *   /url/path/{with}/{params}:
 *     name:     Route Name
 *     handler:  \namespace\handlerClass2
 *     view:     \namespace\viewClass2
 *     action:
 *       get:    getMethodName
 *       post:   postMethodName
 *     middleware:
 *       - \namespace\mwClass3
 * routeGroups:
 *   /url/path/prefix:
 *     file: foo.yml # relative name of file containing def'n for this group
 *                   # (no other properties may be specified if file is used.)
 *     config: { view: \namespace\viewClass2 }
 *     middleware: # as for global setting above
 *     routes: # as for global setting above
 *     routeGroups: # recursive sub-groups, same properties as above.
 *</pre>
 * Changes from the standard Slim programming interface:
 *
 * 1) Route handler classes can expect their constructor to have
 * one argument: a reference to the Slim app object.  This was added
 * so the route handler methods have a way to ref it, since we can't
 * ref it via an anonymous "function() use ($app)..." handler.
 *
 * 2) Additional feature of "routeGroups" allows you to restrict which
 * routes, and middleware get built, based on the path prefix.
 * Only the routeGroup for the longest matching path prefix is loaded.
 * Any "global" routes and middleware are built, and then those
 * specific to the appropriate routeGroup are appended. A routeGroup may
 * additionally define a default view (note that global routes will have
 * been defined using the global default view).
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class SlimConfig
{
    private static $stdActions = [
        'get' => 'get',
        'post' => 'post',
        'put' => 'put',
        'delete' => 'delete',
        'patch' => 'patch',
        'options' => 'options'
    ];

    /**
     * Create a Slim routing service configured from a YAML file.
     *
     * @param string $configFile Filename of config file to read.
     * @return App The Slim Framework application object.
     * @throws ParseException
     */
    public function create(string $configFile): App
    {
        $cachedYaml = new CachedEnvYaml();
        $config = $cachedYaml->fetch($configFile);
        $fileDir = realpath(dirname($configFile));

        return $this->build($config, $fileDir);
    }

    /**
     * Build a Slim routing service configured from an array.
     *
     * @param array $config configuration data.
     * @param string $fileDir directory from which to read included config files.
     * @return App The Slim Framework application object.
     * @throws ParseException
     */
    public function build(array $config, string $fileDir = null)
    {
        try {
            if (isset($config['config'])) {
                $app = new App(new Container($config['config']));
            } else {
                $app = new App(new Container());
            }

            $this->setupRoutes($app, $config, '', $fileDir);

            return $app;
        } catch (ParseException $e) {
            throw new ParseException("Unable to parse Slim configuration", $e->getMessage());
        }
    }

    protected function createView(string $viewClass, array $config)
    {
        return new $viewClass(
            $config['view.templates'] ?? '',
            $config['view.config'] ?? []
        );
    }

    /**
     * Defines default view, middleware, routes and routeGroups.
     *
     * @param App $app - The Slim instance to configure.
     * @param array $config - The configuration hash.
     * @param string $prefix - The current route-group prefix.
     * @param string $fileDir - Full path to the directory containing included route files.
     * @return void
     */
    private function setupRoutes(App $app, array $config, string $prefix, ?string $fileDir)
    {
        $handlerPkg = '';

        if ($fileDir && isset($config['file'])) {
            if (sizeof($config) > 1) {
                throw new InvalidArgumentException(
                    'Cannot use "file" along with other parameters in SlimConfig.'
                );
            }

            $cachedYaml = new CachedEnvYaml();
            $path = ($config['file'][0] === '/' ? '' : ("$fileDir/"));
            $config = $cachedYaml->fetch("$path$config[file]");
        }

        if (isset($config['config']['view'])) {
            $defaultView = $config['config']['view'];
        } else {
            $defaultView = Twig::class;
        }

        $defaultView = $this->createView($defaultView, $config['config']);

        if (isset($config['config']['handler.package'])) {
            $handlerPkg = $config['config']['handler.package'] . '\\';
        }

        if (isset($config['routes'])) {
            foreach ($config['routes'] as $path => &$routeInfo) {
                $routePath = "$prefix$path";

                $view = $routeInfo['view'] ?? null;
                $view = $view ? $this->createView($view, $config['config']) : $defaultView;
                $handler = $routeInfo['handler'];

                if ($handler[0] !== '\\') {
                    $handler = $handlerPkg . $handler;
                }

                $actions = ($routeInfo['action'] ?? []) + self::$stdActions;

                foreach ($actions as $method => $action) {
                    $routeFunc = function ($request, $response, $args) use ($app, $handler, $action, $view) {
                        $obj = $app->getContainer()->get($handler);

                        if (is_subclass_of($obj, BaseRouteHandler::class)) {
                            $obj->doHandlerSetup($request, $response, $view);

                            return call_user_func_array([$obj, $action], $args) ?: $response;
                        } else {
                            throw new InvalidArgumentException(
                                'Requested handler class does not extend BaseRouteHandler'
                            );
                        }
                    };

                    /* @var $route Route */
                    $route = $app->map([strtoupper($method)], $routePath, $routeFunc);

                    if (isset($routeInfo['middleware'])) {
                        foreach (array_reverse($routeInfo['middleware']) as $mwHandler) {
                            $route = $route->add($app->getContainer()->get($mwHandler));
                        }
                    }

                    if (isset($routeInfo['name'])) {
                        // Can specify array of route names per method.
                        if (is_array($routeInfo['name'])) {
                            if (isset($routeInfo['name'][$method])) {
                                $route->setName($routeInfo['name'][$method]);
                            }
                        } else {
                            // Otherwise, name can only apply to one route/method: use first one.
                            $route->setName($routeInfo['name']);
                            unset($routeInfo['name']);
                        }
                    }
                }
            }
        }

        if (isset($config['middleware'])) {
            $wares = $config['middleware'];
            $wares = is_array($wares) ? array_reverse($wares) : [$wares];

            foreach ($wares as $ware) {
                $app->add($app->getContainer()->get($ware));
            }
        }

        // A routeGroup may define additional middleware, routes and routeGroups.
        if (isset($config['routeGroups'])) {
            $uri = $app->getContainer()->get('environment')->get('REQUEST_URI');
            // Make sure shorter paths are checked after longer paths.
            $paths = array_keys($config['routeGroups']);
            rsort($paths);

            foreach ($paths as $path) {
                $fullpath = "$prefix$path";
                $groupInfo = &$config['routeGroups'][$path];

                if (isset($config['config']) && !isset($groupInfo['file'])) {
                    $groupInfo['config'] = $config['config'];
                }

                // Only load the single most appropriate group.
                if (substr($uri, 0, strlen($fullpath)) === $fullpath) {
                    $this->setupRoutes($app, $groupInfo, $fullpath, $fileDir);
                    break;
                }
            }
        }
    }
}
