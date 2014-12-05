<?php

namespace rockunit\cache;

use rock\cache\CacheInterface;
use rock\cache\Exception;
use rock\cache\Redis;

/**
 * @group cache
 * @group redis
 */
class RedisTest extends \PHPUnit_Framework_TestCase
{
    use  CommonTraitTest;

    public static function flush()
    {
        (new Redis())->flush();
    }

    public function init($serialize)
    {
        if (!class_exists('\Redis')) {
            $this->markTestSkipped(
                'The Redis is not available.'
            );
        }
        return new Redis(['serializer' => $serialize]);
    }

    /**
     * @dataProvider providerCache
     */
    public function testGetStorage(CacheInterface $cache)
    {
        $this->assertTrue($cache->getStorage() instanceof \Redis);
    }

    /**
     * @dataProvider providerCache
     * @expectedException Exception
     */
    public function testGetAll(CacheInterface $cache)
    {
        $cache->getAll();
    }
}