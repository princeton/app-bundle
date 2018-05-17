<?php

namespace Princeton\App\Injection;

/**
 * A marker interface for injectable classes.
 *
 * Declaring that a class implements the Injectable interface means that
 * you promise two things:
 *
 *     - Instances of the class are stateless services
 *           (hence a single instance may be shared by multiple dependants.)
 *     - All of the constructor's args are type-hinted
 *           with types which implement Injectable.
 *
 * This allows the class to be used as an auto-wired dependency by the AutowireContainer.
 */
Interface Injectable
{
}
