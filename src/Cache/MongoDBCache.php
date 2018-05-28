<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\MongoDBCache as Doctrine_MongoDBCache;

class MongoDBCache extends Doctrine_MongoDBCache implements Cache
{
}
