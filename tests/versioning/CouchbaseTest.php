<?php
namespace rockunit\core\cache\versioning;

use rock\cache\versioning\Couchbase;
use rock\cache\CacheInterface;
use rockunit\CommonCache;

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
        $this->getStorage()->flush();
    }

    public function init($serialize)
    {
        if (version_compare(PHP_VERSION, '5.6.0', '<')) {
            $this->markTestSkipped(
                'PHP must been 5.6 or higher'
            );
        }
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

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGetStorage(CacheInterface $cache)
    {
        $this->assertTrue($cache->getStorage() instanceof \CouchbaseBucket);
    }

    /**
     * @dataProvider providerCache
     * @expectedException \rock\cache\CacheException
     * @param CacheInterface $cache
     */
    public function testGetAll(CacheInterface $cache)
    {
        $cache->getAll();
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testTtlDecrement(CacheInterface $cache)
    {
        $this->assertEquals($cache->increment('key7', 5), 5, 'should be get: 5');
        $this->assertEquals($cache->decrement('key7', 2, 1), 3, 'should be get: 3');
        sleep(2);
        $this->assertFalse($cache->get('key7'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testHasTtlDecrement(CacheInterface $cache)
    {
        $this->assertEquals($cache->increment('key7', 5), 5, 'should be get: 5');
        $this->assertEquals($cache->decrement('key7', 2, 1), 3, 'should be get: 3');
        sleep(2);
        $this->assertFalse($cache->exists('key7'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGetTag(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key25', ['one', 'two'], 0, ['tag_18', 'tag_17']));
        $this->assertTrue($cache->set('key26', 'three', 0, ['tag_18']));
        $this->assertInternalType('string', $cache->getTag('tag_18'), 'var should be type string');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testRemoveTag(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $timestamp = $cache->getTag('bar');
        $this->assertTrue($cache->removeTag('bar'), 'tag "bar" does not remove');
        $this->assertFalse($cache->get('key1'), 'should be get: false');
        $this->assertNotEquals($cache->getTag('bar'), $timestamp, 'timestamps does not equals');
    }

    /**
     * @dataProvider providerCache
     * @expectedException \rock\cache\CacheException
     * @param CacheInterface $cache
     */
    public function testGetAllKeys(CacheInterface $cache)
    {
        $cache->getAllKeys();
    }
} 