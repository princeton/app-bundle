<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\ExtMongoDBCache as Doctrine_ExtMongoDBCache;

class ExtMongoDBCache extends Doctrine_ExtMongoDBCache implements Cache
{
}
