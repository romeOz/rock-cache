<?php
namespace rockunit;

use rock\cache\CacheInterface;
use rock\cache\Couchbase;

/**
 * @group couchbase
 * @group cache
 */
class CouchbaseTest extends CommonCache
{
    public function getStorage(array $config = [])
    {
        $config['servers'] = [[
            'host' => 'couchbase',
            'port' => 8091
        ]];

        return new Couchbase($config);
    }

    public function setUp()
    {
        if (!class_exists('\CouchbaseBucket')) {
            $this->markTestSkipped(
                'The Couchbase is not available.'
            );

        } elseif(defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                'Couchbase does not seem to support HHVM right now.'
            );
        }

        $this->getStorage()->flush();
    }

    public function init($serialize)
    {
        if (!class_exists('\CouchbaseBucket')) {
            $this->markTestSkipped(
                'The Couchbase is not available.'
            );

        } elseif(defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                'Couchbase does not seem to support HHVM right now.'
            );
        }
        return $this->getStorage(['serializer' => $serialize]);
    }

    // tests:

    /**
     * @dataProvider providerCache
     */
    public function testGetStorage(CacheInterface $cache)
    {
        $this->assertTrue($cache->getStorage() instanceof \CouchbaseBucket);
    }

    /**
     * @dataProvider providerCache
     * @expectedException \rock\cache\CacheException
     */
    public function testGetAll(CacheInterface $cache)
    {
        $cache->getAll();
    }

    /**
     * @dataProvider providerCache
     * @expectedException \rock\cache\CacheException
     */
    public function testGetAllKeys(CacheInterface $cache)
    {
        $cache->getAllKeys();
    }

    /**
     * @dataProvider providerCache
     */
    public function testDecrement(CacheInterface $cache)
    {
        /** @var $this \PHPUnit_Framework_TestCase */

        $this->assertEquals(5, $cache->increment('key7', 5), 'should be get: 5');
        $this->assertEquals(3, $cache->decrement('key7', 2), 'should be get: 3');
        $this->assertEquals(3, $cache->get('key7'), 'should be get: 3');

        $this->assertEquals(0, $cache->decrement('key17', 2), 'should be get: 0');
    }
} 