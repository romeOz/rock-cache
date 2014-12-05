<?php
namespace rockunit\cache;

use rock\cache\CacheInterface;
use rock\cache\Exception;
use rock\cache\Memcache;

/**
 * @group cache
 * @group memcache
 */
class MemcacheTest extends \PHPUnit_Framework_TestCase
{
    use  CommonTraitTest;

    public static function flush()
    {
        (new Memcache())->flush();
    }

    public function init($serialize)
    {
        if (!class_exists('\Memcache')) {
            $this->markTestSkipped(
                'The Memcache is not available.'
            );
        }
        return new Memcache(['serializer' => $serialize]);
    }

    /**
     * @dataProvider providerCache
     */
    public function testGetStorage(CacheInterface $cache)
    {
        $this->assertTrue($cache->getStorage() instanceof \Memcache);
    }

    /**
     * @dataProvider providerCache
     * @expectedException Exception
     */
    public function testGetAll(CacheInterface $cache)
    {
        $cache->getAll();
    }

    /**
     * @dataProvider providerCache
     * @expectedException Exception
     */
    public function testGetAllKeys(CacheInterface $cache)
    {
        $cache->getAllKeys();
    }
}