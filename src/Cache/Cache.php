<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\Cache as Doctrine_Cache;
use Doctrine\Common\Cache\FlushableCache;
use Princeton\App\Injection\Injectable;

interface Cache extends Doctrine_Cache, FlushableCache, Injectable
{
}
