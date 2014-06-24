<?php
namespace rockunit\cache;

use rock\cache\Memcached;
use rockunit\TestCase;

class MemcachedTest extends TestCase
{
    public static function flush()
    {
        (new Memcached(['enabled' => true]))->flush();
    }

    public function init($serialize)
    {
        $cache = new Memcached(['enabled' => true, 'serializer' => $serialize]);
        return $cache;
    }
}
 