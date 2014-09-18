<?php

namespace Princeton\App\Slim;

use Slim\Slim;
use Symfony\Component\Yaml\Exception\ParseException;
use Princeton\App\Cache\CachedYaml;

/**
 * Implements a YAML-based route configurator for Slim.
 *
 * Supports the following structure:
 *<pre>
 * name:    App Name
 * config:
 *   mode:  development
 *   log.enabled: true
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
 *     config: { view: \namespace\viewClass2 }
 *     hooks: # as for global setting above
 *     middleware: # as for global setting above
 *     routes: # as for global setting above
 *</pre>
 * Changes from the standard Slim programming interface:
 * 1) Route handler classes can expect their constructor to have
 * one argument: a reference to the Slim app object.  This was added
 * so the route handler methods have a way to ref it, since we can't
 * ref it via an anonymous "function() use ($slim)..." handler.
 * 2) Additional feature of "routeGroups" allows you to restrict which
 * routes, hooks, and middleware get built, based on the path prefix.
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
	 * @return Slim The Slim Framework object.
	 * @throws ParseException
	 */
	public function create($configFile)
	{
		try {
			$cachedYaml = new CachedYaml();
			$config = $cachedYaml->fetch($configFile);
			
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

			if (isset($config['name'])) {
				$slim->setName($config['name']);
			}

			/* Define global default view, middleware, hooks and routes. */
			$this->setupRoutes($slim, $config, '');

			/* A routeGroup may define additional middleware, hooks and routes. */
			if (isset($config['routeGroups'])) {
				foreach ($config['routeGroups'] as $path => &$groupInfo) {
					if (substr($slim->request->getPathInfo(), 0, strlen($path)) === $path) {
						$this->setupRoutes($slim, $groupInfo, $path);
						break;
					}
				}
			}
			
			return $slim;
		} catch (ParseException $e) {
			throw new ParseException("Unable to parse Slim configuration", $e->getMessage());
		}
	}
	
	/**
	 *
	 * @param Slim $slim
	 * @param array $config
	 * @return void
	 */
	private function setupRoutes($slim, $config, $prefix)
	{
		if (isset($config['config']['view'])) {
			$defaultView = $config['config']['view'];
		} else {
			$defaultView = '\Slim\View';
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
    			$mapArgs = array($prefix . $path);
    			if (isset($routeInfo['middleware'])) {
    				foreach ($routeInfo['middleware'] as $mwHandler => &$mwAction) {
    					$mapArgs[] = array($mwHandler, $mwAction);
    				}
    			}
    			$view = isset($routeInfo['view']) ? $routeInfo['view'] : $defaultView;
    			$handler = $routeInfo['handler'];
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
    						
    					}
    				};
    				/* @var $route \Slim\Route */
    				$route = call_user_func_array(array($slim, 'map'), $mapArgs)
    					->via(strtoupper($method));
    				array_pop($mapArgs);
    				if (isset($routeInfo['name'])) {
    					$route->name($routeInfo['name']);
    				}
    				if (isset($routeInfo['conditions'])) {
    					$route->conditions($routeInfo['conditions']);
    				}
    			}
    		}
		}
	}
}
