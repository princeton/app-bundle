<?php

namespace Princeton\App\Cache;

use Doctrine\Common\Cache\Cache as Doctrine_Cache;
use Princeton\App\Injection\Injectable;

interface Cache extends Doctrine_Cache, Injectable
{
}
