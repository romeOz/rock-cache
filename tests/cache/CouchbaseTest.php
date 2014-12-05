<?php
namespace rockunit\cache;

use rock\cache\CacheInterface;
use rock\cache\Exception;
use rock\cache\Couchbase;

/**
 * @group cache
 * @group couchbase
 */
class CouchbaseTest extends \PHPUnit_Framework_TestCase
{
    use  CommonTraitTest;

    public static function flush()
    {
        (new Couchbase())->flush();
    }

    public function init($serialize)
    {
        if (!class_exists('\Couchbase')) {
            $this->markTestSkipped(
                'The Couchbase is not available.'
            );
        }
        return new Couchbase(['serializer' => $serialize]);
    }

    /**
     * @dataProvider providerCache
     */
    public function testGetStorage(CacheInterface $cache)
    {
        $this->assertTrue($cache->getStorage() instanceof \Couchbase);
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