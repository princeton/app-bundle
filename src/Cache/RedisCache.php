<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\RedisCache as Doctrine_RedisCache;

class RedisCache extends Doctrine_RedisCache implements Cache
{
}
