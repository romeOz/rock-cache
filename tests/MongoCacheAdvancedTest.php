<?php

namespace rockunit;

use rock\cache\CacheInterface;
use rock\helpers\Instance;
use rock\cache\MongoCache;

/**
 * @group mongodb
 */
class MongoCacheAdvancedTest extends MongoDbTestCase
{
    /**
     * @var string test cache collection name.
     */
    protected static $cacheCollection = '_test_cache';

    protected function tearDown()
    {
        $this->dropCollection(static::$cacheCollection);
        parent::tearDown();
    }

    public function init($serialize, $lock)
    {
    }

    public function setUp()
    {
        $this->createCache()->flush();
    }


    /**
     * Creates test cache instance.
     * @return \rock\cache\MongoCache cache instance.
     */
    protected function createCache()
    {
        if (!class_exists('\MongoClient')) {
            $this->markTestSkipped(
                'The \MongoClient is not available.'
            );
        }
        $connection = $this->getConnection();
        $connection
            ->getCollection(static::$cacheCollection)
            ->createIndex('id', ['unique' => true])
            ->createIndex('expire', ['expireAfterSeconds' => 0]);
        return Instance::ensure([
            'class' => MongoCache::className(),
            'storage' => $connection,
            'cacheCollection' => static::$cacheCollection,
            'hashKey' => 0,
        ]);
    }

    public function providerCache()
    {
        return array(
            [$this->createCache()],
        );
    }

    /**
     * @dataProvider providerCache
     */
    public function testGetStorage(CacheInterface $cache)
    {
        $this->assertTrue($cache->getStorage() instanceof \rock\mongodb\Connection);
    }


    /**
     * @dataProvider providerCache
     * @expectedException \rock\cache\CacheException
     */
    public function testGetTag(CacheInterface $cache)
    {
        /** @var $this \PHPUnit_Framework_TestCase */
        $cache->getTag('foo');
    }


    /**
     * @dataProvider providerCache
     * @expectedException \rock\cache\CacheException
     */
    public function testGetTags(CacheInterface $cache)
    {
        /** @var $this \PHPUnit_Framework_TestCase */

        $cache->getMultiTags(['bar', 'foo']);
    }

    /**
     * @dataProvider providerCache
     * @expectedException \rock\cache\CacheException
     */
    public function testExistsTag(CacheInterface $cache)
    {
        $cache->existsTag('foo');
    }

    /**
     * @dataProvider providerCache
     * @expectedException \rock\cache\CacheException
     */
    public function testExistsTagFalse(CacheInterface $cache)
    {
        $cache->existsTag('baz');
    }

    /**
     * @dataProvider providerCache
     */
    public function testRemoveTag(CacheInterface $cache)
    {
        /** @var $this \PHPUnit_Framework_TestCase */

        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $this->assertTrue($cache->removeTag('bar'), 'should be get: true');
        $this->assertFalse($cache->get('key1'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     */
    public function testGetAllKeys(CacheInterface $cache)
    {
        /** @var $this \PHPUnit_Framework_TestCase */

        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $expected = $cache->getAllKeys();
        if ($expected !== false) {
            $actual = [
                $cache->prepareKey('key1'),
                $cache->prepareKey('key2'),
            ];
            sort($expected);
            sort($actual);
            $this->assertEquals($expected, $actual, 'should be get: ' . json_encode($actual));
        }
    }

    /**
     * @dataProvider providerCache
     * @expectedException \rock\cache\CacheException
     */
    public function testStatus(CacheInterface $cache)
    {
        $cache->status();
    }
}