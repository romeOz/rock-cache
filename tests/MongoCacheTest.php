<?php

namespace rockunit;

use rock\cache\CacheInterface;
use rock\helpers\Instance;
use rock\cache\MongoCache;

/**
 * @group mongodb
 */
class MongoCacheTest extends CommonCache
{
    use MongoDbTestCase {
        MongoDbTestCase::tearDown as parentTearDown;
    }

    /**
     * @var string test cache collection name.
     */
    protected $cacheCollection = '_test_cache';

    /**
     * Creates test cache instance.
     * @return \rock\cache\MongoCache cache instance.
     */
    protected function getStorage()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('mongo extension required.');
        }
        if (!class_exists('\MongoClient')) {
            $this->markTestSkipped(
                'The \MongoClient is not available.'
            );
        }
        if (!class_exists('\rock\mongodb\Connection')) {
            $this->markTestSkipped("Doesn't installed Rock MongoDB.");
        }

        $connection = $this->getConnection();
        $collection = $connection->getCollection($this->cacheCollection);
        $collection->createIndex('id', ['unique' => true]);
        $collection->createIndex('expire', ['expireAfterSeconds' => 0]);
        return Instance::ensure([
            'class' => MongoCache::className(),
            'storage' => $connection,
            'cacheCollection' => $this->cacheCollection,
            'hashKey' => 0,
        ]);
    }

    protected function tearDown()
    {
        $this->dropCollection($this->cacheCollection);
        $this->parentTearDown();
    }

    public function setUp()
    {
        $this->getStorage()->flush();
    }

    public function providerCache()
    {
        return [
            [$this->getStorage()],
        ];
    }


    // Tests:

    /**
     * @dataProvider providerCache
     */
    public function testGetStorage(CacheInterface $cache)
    {
        //var_dump('dfdf');
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
     * @param CacheInterface $cache
     * @expectedException \rock\cache\CacheException
     */
    public function testGetHashMd5Tags(CacheInterface $cache)
    {
        parent::testGetHashMd5Tags($cache);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     * @expectedException \rock\cache\CacheException
     */
    public function testGetHashSHATags(CacheInterface $cache)
    {
        parent::testGetHashSHATags($cache);
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

    /**
     * @dataProvider providerCache
     */
    public function testFlush(CacheInterface $cache)
    {
        $cache->set('key1', 'value1');
        $cache->set('key2', 'value2');

        $this->assertTrue($cache->flush(), 'Unable to flush cache!');

        $collection = $cache->getStorage()->getCollection($this->cacheCollection);
        $rows = $this->findAll($collection);
        $this->assertCount(0, $rows, 'Unable to flush records!');
    }
}