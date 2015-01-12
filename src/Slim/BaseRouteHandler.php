<?php

namespace Princeton\App\Slim;

/**
 * Handles requests for templates
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class BaseRouteHandler
{
	protected $slim;

	/* @var $slim \Slim\Slim */
	public function __construct($slim) {
		$this->slim = $slim;
	}
	
	/* To be overridden by subclasses. */
	public function get() { $this->notAllowed(); }
	public function post() { $this->notAllowed(); }
	public function put($id) { $this->notAllowed(); }
	public function patch($id) { $this->notAllowed(); }
	public function delete($id) { $this->notAllowed(); }
	public function options() { $this->notAllowed(); }

	protected function render($page, $data)
	{
		$this->slim->render($page, $data);
	}

	protected function forbidden()
	{
		// Forbidden.
		$this->slim->response->setStatus(403);
	}

	protected function notFound()
	{
		// Not Found.
		$this->slim->response->setStatus(404);
	}

	protected function notAllowed()
	{
		// Method Not Allowed.
		$this->slim->response->headers->set('Allow', '');
		$this->slim->response->setStatus(405);
	}
}
