<?php

namespace rockunit\cache;

use rock\cache\Redis;

class RedisTest extends \PHPUnit_Framework_TestCase
{
    use  CommonTraitTest;

    public static function flush()
    {
        (new Redis(['enabled' => true]))->flush();
    }

    public function init($serialize)
    {
        return new Redis(['enabled' => true, 'serializer' => $serialize]);
    }
}
 