<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\PhpFileCache as Doctrine_PhpFileCache;

class PhpFileCache extends Doctrine_PhpFileCache implements Cache
{
}
