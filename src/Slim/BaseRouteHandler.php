<?php

namespace Princeton\App\Slim;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Views\Twig;

/**
 * Handles requests for templates
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class BaseRouteHandler
{
    protected $request;

    protected $response;

    protected $view;

    /**
     * Do the SlimConfig handler setup.
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Twig $view
     */
    public function doHandlerSetup(ServerRequestInterface $request, ResponseInterface $response, Twig $view)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->postHandlerSetup();
    }

    public function postHandlerSetup() { }

    /* To be overridden by subclasses. */
    public function get() { return self::notAllowed(); }
    public function post() { return self::notAllowed(); }
    public function put($id) { return self::notAllowed(); }
    public function patch($id) { return self::notAllowed(); }
    public function delete($id) { return self::notAllowed(); }
    public function options() { return self::notAllowed(); }

    protected function render($page, $data)
    {
        return $this->response = $this->view->render($this->response, $page, $data);
    }

    protected function forbidden()
    {
        // Forbidden.
        return $this->response = $this->response->withStatus(403);
    }

    protected function notFound()
    {
        // Not Found.
        return $this->response = $this->response->withStatus(404);
    }

    protected function notAllowed()
    {
        // Method Not Allowed.
        return $this->response = $this->response->withHeader('Allow', '')->withStatus(405);
    }

    /**
     * Get the JSON request body as an associative array.
     *
     * @return array
     */
    protected function getRequestData()
    {
        return json_decode($this->request->getBody(), true);
    }
}
