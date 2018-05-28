<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\ArrayCache as Doctrine_ArrayCache;

class ArrayCache extends Doctrine_ArrayCache implements Cache
{
}
