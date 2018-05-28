<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\MemcacheCache as Doctrine_MemcacheCache;

class MemcacheCache extends Doctrine_MemcacheCache implements Cache
{
}
