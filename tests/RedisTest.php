<?php

namespace rockunit;

use rock\cache\CacheInterface;
use rock\cache\Redis;

/**
 * @group cache
 * @group redis
 */
class RedisTest extends CommonCache
{
    public function getStorage(array $config = [])
    {
        $config['server'] = [
            'host' => $_SERVER["REDIS_PORT_6379_TCP_ADDR"],
            'port' => 6379
        ];

        return new Redis($config);
    }

    public function setUp()
    {
        if (!class_exists('\Redis')) {
            $this->markTestSkipped(
                'The \Redis is not available.'
            );
        }

        $this->getStorage()->flush();
    }

    public function init($serialize)
    {
        if (!class_exists('\Redis')) {
            $this->markTestSkipped(
                'The \Redis is not available.'
            );
        }
        return $this->getStorage(['serializer' => $serialize]);
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
     * @expectedException \rock\cache\CacheException
     */
    public function testGetAll(CacheInterface $cache)
    {
        $cache->getAll();
    }
}
 