<?php

namespace rockunit;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Cache\Adapter;
use rock\cache\CacheFile;
use rock\cache\CacheInterface;
use rock\file\FileManager;
use rockunit\common\CommonTestTrait;

/**
 * @group cache
 * @group local
 */
class CacheFileTest extends CommonCache
{
    use CommonTestTrait;

    /** @var FileManager */
    protected static $fileManager;

    protected function setUp()
    {
        parent::setUp();
        static::flush();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        static::clearRuntime();
    }

    public function init($serialize, $lock = true)
    {
        return new CacheFile([
           'adapter' => static::getFileManager(),
           'serializer' => $serialize
        ]);
    }

    /**
     * @dataProvider providerCache
     * @expectedException \rock\cache\CacheException
     */
    public function testGetStorage(CacheInterface $cache)
    {
        $cache->getStorage();
    }

    /**
     * @dataProvider providerCache
     */
    public function testGetAll(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key5', 'foo'), 'should be get: true');
        $this->assertTrue($cache->set('key6', ['bar', 'baz']), 'should be get: true');
        $this->assertTrue(is_array($cache->getAll()));
        $this->assertSame(count($cache->getAll()), 2);
    }

    /**
     * @dataProvider providerCache
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
     */
    public function testGetAllKeys(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $expected = $cache->getAllKeys();
        $actual = [
            $cache->prepareKey('key1'),
            $cache->prepareKey('key2'),
        ];
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual, 'should be get: ' . json_encode($actual));
    }

    /**
     * @dataProvider providerCache
     * @expectedException \rock\cache\CacheException
     */
    public function testStatus(CacheInterface $cache)
    {
        $cache->status();
    }

    public static function flush()
    {
        (new CacheFile(
            [
                'adapter' => static::getFileManager(),
            ]
        ))->flush();
        static::getFileManager()->flushCache();
    }

    /**
     * @return FileManager
     */
    protected static function getFileManager()
    {
        if (!isset(static::$fileManager)) {
            $local = new Local(ROCKUNIT_RUNTIME);
            $config = [
                'adapter' => new Local(ROCKUNIT_RUNTIME . '/cache'),
                'cache' => new Adapter($local, 'cache.tmp')
            ];
            static::$fileManager = new FileManager($config);
        }

        return static::$fileManager;
    }
}