<?php

namespace rockunit\cache;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Cache\Adapter;
use rock\cache\CacheFile;
use rock\cache\CacheInterface;
use rock\cache\Exception;
use rock\cache\filemanager\FileManager;

class CacheFileTest extends \PHPUnit_Framework_TestCase
{
    use  CommonTraitTest;

    /** @var FileManager */
    protected static $fileManager;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::clearRuntime();
    }

    public static function flush()
    {
        (new CacheFile(
            [
                'enabled' => true,
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
            static::$fileManager = new FileManager(
                [
                    'adapter' =>
                        function () {
                            return new Local(RUNTIME . '/cache');
                        },
                    'cache' => function () {
                            $local = new Local(RUNTIME);
                            $cache = new Adapter($local, 'cache.tmp');

                            return $cache;
                        }
                ]
            );
        }

        return static::$fileManager;
    }

    public function init($serialize)
    {
        return new CacheFile([
           'enabled' => true,
           'adapter' => static::getFileManager(),
           'serializer' => $serialize
        ]);
    }

    /**
     * @dataProvider providerCache
     * @expectedException Exception
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
        $this->assertFalse($cache->has('key7'), 'should be get: false');
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

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        static::clearRuntime();
    }

    protected static function clearRuntime()
    {
        $runtime = RUNTIME;
        @rmdir("{$runtime}/cache");
        @rmdir("{$runtime}/filesystem");
        @unlink("{$runtime}/cache.tmp");
        @unlink("{$runtime}/filesystem.tmp");
    }
}