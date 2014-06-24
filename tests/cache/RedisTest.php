<?php

namespace rockunit\cache;

use rock\cache\Redis;
use rockunit\TestCase;

class RedisTest extends TestCase
{
    public static function flush()
    {
        (new Redis(['enabled' => true]))->flush();
    }

    public function init($serialize)
    {
        $cache = new Redis(['enabled' => true, 'serializer' => $serialize]);
        return $cache;
    }
}
 