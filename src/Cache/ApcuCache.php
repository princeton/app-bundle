<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\ApcuCache as Doctrine_ApcuCache;

class ApcuCache extends Doctrine_ApcuCache implements Cache
{
}
