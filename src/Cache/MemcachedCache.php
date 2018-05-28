<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\MemcachedCache as Doctrine_MemcachedCache;

class MemcachedCache extends Doctrine_MemcachedCache implements Cache
{
}
