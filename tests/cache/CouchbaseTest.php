<?php
namespace rockunit\cache;

use rock\cache\CacheInterface;
use rock\cache\Exception;
use rock\cache\Couchbase;

class CouchbaseTest extends \PHPUnit_Framework_TestCase
{
    use  CommonTraitTest;

    public static function flush()
    {
        (new Couchbase(['enabled' => true]))->flush();
    }

    public function init($serialize)
    {
        return new Couchbase(['enabled' => true, 'serializer' => $serialize]);
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