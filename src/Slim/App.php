<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2018 The Trustees of Princeton University.
 */

namespace Princeton\App\Slim;

use Psr\Http\Message\ResponseInterface;
use Slim\App as Slim_App;

/**
 * A slight modification to Slim\App.
 * The Response object should "own" any headers it attempts to set,
 * overriding any settings that may have been set directly with the headers() function.
 * The vanilla Slim\App behavior is to add our Response's headers to the pre-existing ones.
 */
class App extends Slim_App {
    /**
     * Removes any headers that may have been set with headers() which would
     * conflict with headers set in our Response object, before calling \Slim\App::respond().
     * {@inheritDoc}
     * @see \Slim\App::respond()
     */
    public function respond(ResponseInterface $response)
    {
        if (!headers_sent()) {
            foreach ($response->getHeaders() as $name => $values) {
                header_remove($name);
            }
        }

        return parent::respond($response);
    }
}
