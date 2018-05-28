<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\CouchbaseCache as Doctrine_CouchbaseCache;

class CouchbaseCache extends Doctrine_CouchbaseCache implements Cache
{
}
