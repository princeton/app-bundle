<?php

namespace Princeton\App\Slim;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Views\Twig;
use Princeton\App\Injection\Injectable;

/**
 * Handles requests for templates
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class BaseRouteHandler implements Injectable
{
    protected $slim;
    protected $view;
    protected $request;
    protected $response;

    /**
     * Do the SlimConfig handler setup.
     * @param App $app
     * @param Twig $view
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function doHandlerSetup(App $app, Twig $view, ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->slim = $app;
        $this->view = $view;
        $this->request = $request;
        $this->response = $response;
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
