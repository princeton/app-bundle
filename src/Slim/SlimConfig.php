<?php

namespace Princeton\App\Slim;

use Slim\Slim;
use Symfony\Component\Yaml\Exception\ParseException;
use Princeton\App\Cache\CachedYaml;
use Slim\LogWriter;

/**
 * Implements a YAML-based route configurator for Slim.
 *
 * Supports the following structure:
 *<pre>
 * name:    App Name
 * config:
 *   mode:  development
 *   log.enabled: true
 *   log.file: /path/to/log/file # (defaults to stderr OR ini 'error_log' setting)
 *   debug: true
 *   view:  \namespace\viewClass1
 *   # etc....
 * hooks:
 *   'hook.name.1':
 *     handler: \namespace\handlerClass1
 *     action:  actionMethodName
 *     priority: 10
 * middleware: [ \namespace\mwClass1, \namespace\mwClass2 ]
 * routes:
 *   /url/path/:with/:params:
 *     name:     Route Name
 *     handler:  \namespace\handlerClass2
 *     view:     \namespace\viewClass2
 *     action:
 *       get:    getMethodName
 *       post:   postMethodName
 *     conditions:
 *       param:   matchString
 *     middleware:
 *       \namespace\mwClass3: actionMethodName
 * routeGroups:
 *   /url/path/prefix:
 *     file: foo.yml # relative name of file containing def'n for this group
 *                   # (no other properties may be specified if file is used.)
 *     config: { view: \namespace\viewClass2 }
 *     hooks: # as for global setting above
 *     middleware: # as for global setting above
 *     routes: # as for global setting above
 *     routeGroups: # recursive sub-groups, same properties as above.
 *</pre>
 * Changes from the standard Slim programming interface:
 *
 * 1) Route handler classes can expect their constructor to have
 * one argument: a reference to the Slim app object.  This was added
 * so the route handler methods have a way to ref it, since we can't
 * ref it via an anonymous "function() use ($slim)..." handler.
 *
 * 2) Additional feature of "routeGroups" allows you to restrict which
 * routes, hooks, and middleware get built, based on the path prefix.
 * Only the routeGroup for the longest matching path prefix is loaded.
 * Any "global" routes, hooks and middleware are built, and then those
 * specific to the appropriate routeGroup are appended. A routeGroup may
 * additionally define a default view (note that global routes will have
 * been defined using the global default view).
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class SlimConfig
{
    private static $stdActions = array(
        'get' => 'get',
        'post' => 'post',
        'put' => 'put',
        'delete' => 'delete',
        'patch' => 'patch',
        'options' => 'options'
    );

    /**
     * Create a Slim routing service configured from a YAML file.
     *
     * @param string $configFile Filename of config file to read.
     *
     * @return Slim The Slim Framework object.
     * @throws ParseException
     */
    public function create($configFile)
    {
        try {
            $cachedYaml = new CachedYaml();
            $config = $cachedYaml->fetch($configFile);
            $fileDir = realpath(dirname($configFile));
            
            if (isset($config['config'])) {
                if (isset($config['config']['log.level'])) {
                    $level = strtoupper($config['config']['log.level']);
                    switch ($level) {
                        case 'ALERT':
                            $level = \Slim\Log::ALERT;
                            break;
                        case 'CRITICAL':
                            $level = \Slim\Log::CRITICAL;
                            break;
                        case 'DEBUG':
                            $level = \Slim\Log::DEBUG;
                            break;
                        case 'EMERGENCY':
                            $level = \Slim\Log::EMERGENCY;
                            break;
                        case 'ERROR':
                            $level = \Slim\Log::ERROR;
                            break;
                        case 'FATAL':
                            $level = \Slim\Log::FATAL;
                            break;
                        case 'INFO':
                            $level = \Slim\Log::INFO;
                            break;
                        case 'NOTICE':
                            $level = \Slim\Log::NOTICE;
                            break;
                        case 'WARN':
                            $level = \Slim\Log::WARN;
                            break;
                    }
                    $config['config']['log.level'] = $level;
                }
                $slim = new Slim($config['config']);
            } else {
                $slim = new Slim();
            }

            if (isset($config['config']['log.file'])) {
            	$errorResource = @fopen($config['config']['log.file'], 'a');
            } else if (ini_get('error_log')) {
            	$errorResource = @fopen(ini_get('error_log'), 'a');
            }
            if (isset($errorResource)) {
            	$slim->{'log'}->setWriter(new LogWriter($errorResource));
            }

            if (isset($config['name'])) {
                $slim->setName($config['name']);
            }

            $this->setupRoutes($slim, $config, '', $fileDir);
            
            return $slim;
        } catch (ParseException $e) {
            throw new ParseException("Unable to parse Slim configuration", $e->getMessage());
        }
    }
    
    /**
     * Defines default view, middleware, hooks, routes and routeGroups.
     *
     * @param Slim $slim - The Slim instance to configure.
     * @param array $config - The configuration hash.
     * @param string $prefix - The current route-group prefix.
     * @param string $fileDir - Full path to the directory containing included route files.
     *
     * @return void
     */
    private function setupRoutes($slim, $config, $prefix, $fileDir)
    {
        $handlerPkg = '';
        
        if (isset($config['file'])) {
            if (count($config) > 1) {
                throw new \InvalidArgumentException('Cannot use "file" along with other parameters in SlimConfig.');
            }
            $cachedYaml = new CachedYaml();
            $config = $cachedYaml->fetch($fileDir . '/' . $config['file']);
        }
        
        if (isset($config['config']['view'])) {
            $defaultView = $config['config']['view'];
        } else {
            $defaultView = '\Slim\View';
        }
        
        if (isset($config['config']['handler.package'])) {
            $handlerPkg = $config['config']['handler.package'] . '\\';
        }
        
        if (isset($config['middleware'])) {
            if (is_array($config['middleware'])) {
                foreach ($config['middleware'] as $ware) {
                    $slim->add(new $ware);
                }
            } else {
                $slim->add(new $config['middleware']);
            }
        }

        if (isset($config['hooks'])) {
            foreach ($config['hooks'] as $name => $info) {
                $slim->hook($name, array($info['handler'], $info['action']), isset($info['priority']) ? $info['priority'] : 10);
            }
        }

        if (isset($config['routes'])) {
            foreach ($config['routes'] as $path => &$routeInfo) {
                $mapArgs = array("$prefix$path");
                if (isset($routeInfo['middleware'])) {
                    foreach ($routeInfo['middleware'] as $mwHandler => &$mwAction) {
                        $mapArgs[] = array($mwHandler, $mwAction);
                    }
                }
                $view = isset($routeInfo['view']) ? $routeInfo['view'] : $defaultView;
                $handler = $routeInfo['handler'];
                if ($handler[0] !== '\\') {
                    $handler = $handlerPkg . $handler;
                }
                $actions = isset($routeInfo['action']) ? ($routeInfo['action'] + self::$stdActions) : self::$stdActions;
                foreach ($actions as $method => $action) {
                    $mapArgs[] = function () use ($slim, $handler, $action, $view) {
                        // handler can expect $slim as constructor arg!
                        $slim->view($view);
                        $obj = new $handler($slim);
                        if (is_subclass_of($obj, '\Princeton\App\Slim\BaseRouteHandler')) {
                            $args = func_get_args();
                            call_user_func_array(array($obj, $action), $args);
                        } else {
                            throw new \InvalidArgumentException('Requested handler class does not extend BaseRouteHandler');
                        }
                    };
                    /* @var $route \Slim\Route */
                    $route = call_user_func_array(array($slim, 'map'), $mapArgs)
                        ->via(strtoupper($method));
                    array_pop($mapArgs);
                    if (isset($routeInfo['name'])) {
                    	// Can specify array of route names per method.
                    	if (is_array($routeInfo['name'])) {
                    		if (isset($routeInfo['name'][$method])) {
                    			$route->name($routeInfo['name'][$method]);
                    		}
                    	} else {
                    		// Otherwise, name can only apply to one route/method: use first one.
	                        $route->name($routeInfo['name']);
    	                    unset($routeInfo['name']);
                    	}
                    }
                    if (isset($routeInfo['conditions'])) {
                        $route->conditions($routeInfo['conditions']);
                    }
                }
            }
        }
            
        // A routeGroup may define additional middleware, hooks, routes and routeGroups.
        if (isset($config['routeGroups'])) {
            // Make sure shorter paths are checked after longer paths.
            $paths = array_keys($config['routeGroups']);
            rsort($paths);
            foreach ($paths as $path) {
                $groupInfo = &$config['routeGroups'][$path];
                // Only load the single most appropriate group.
                if (substr($slim->request->getPathInfo(), 0, strlen($path)) === $path) {
                    $this->setupRoutes($slim, $groupInfo, "$prefix$path", $fileDir);
                    break;
                }
            }
        }
    }
}
