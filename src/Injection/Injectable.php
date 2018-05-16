<?php

namespace Princeton\App\Injection;

/**
 * Declaring that a class implements the Injectable interface
 * means that you promise two things:
 *     - Instances of the class are stateless services
 *     - All of its constructor args also implement Injectable
 */
Interface Injectable { }
