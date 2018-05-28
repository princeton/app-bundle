<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\SQLite3Cache as Doctrine_SQLite3Cache;

class SQLite3Cache extends Doctrine_SQLite3Cache implements Cache
{
}
