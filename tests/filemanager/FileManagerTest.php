<?php

namespace rockunit\filemanager;


use League\Flysystem\Adapter\Local;
use League\Flysystem\Cache\Adapter;
use League\Flysystem\Cache\Memcached;
use rock\cache\filemanager\FileManager;

class FileManagerTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::clearRuntime();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testHasSuccess(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->write('test/bar.tmp', 'bar'));
        $this->assertTrue($fileManager->has('test/bar.tmp', FileManager::TYPE_FILE));
        $this->assertTrue($fileManager->has('test', FileManager::TYPE_DIR));
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testHasFail(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->write('test/bar.tmp', 'bar'));
        $this->assertFalse($fileManager->has('test/bar.tmp', FileManager::TYPE_DIR));
        $this->assertFalse($fileManager->has('test', FileManager::TYPE_FILE));
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testWriteSuccess(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->has('foo.tmp'));
        $this->assertTrue($fileManager->delete('foo.tmp'));
        $this->assertFalse($fileManager->has('foo.tmp'));
        $this->assertTrue($fileManager->write('0', 'hh'));
        $this->assertTrue($fileManager->has('0'));
        $this->assertTrue($fileManager->delete('0'));
        $this->assertFalse($fileManager->has('0'));
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testWriteFail(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertFalse($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->delete('foo.tmp'));
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testPut(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->put('foo.tmp', 'foo'));
        $this->assertSame($fileManager->read('foo.tmp'), 'foo');
        $this->assertTrue($fileManager->put('foo.tmp', 'test'));
        $this->assertSame($fileManager->read('foo.tmp'), 'test');
        $this->assertTrue($fileManager->delete('foo.tmp'));
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testDeleteSuccess(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->delete('~/^foo/'));
        $this->assertFalse($fileManager->delete('~/^foo/'));
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testDeleteFail(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertFalse($fileManager->delete('foo'));
        $this->assertFalse($fileManager->delete('~/foo$/'));
        $this->assertTrue($fileManager->delete('foo.tmp'));
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testReadSuccess(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertSame($fileManager->read('~/^foo/'), 'foo');
        $this->assertSame($fileManager->read('foo.tmp'), 'foo');
        $this->assertTrue($fileManager->delete('foo.tmp'));
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testReadFail(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertFalse($fileManager->read('~/foo$/'));
        $this->assertFalse($fileManager->read('foo'));
        $this->assertSame(
            $fileManager->getErrors(),
            array(
                'Unknown file: /foo$/',
                'Unknown file: foo',
            )
        );
        $this->assertTrue($fileManager->delete('foo.tmp'));
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testReadAndDeleteSuccess(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertSame($fileManager->readAndDelete('~/^foo/'), 'foo');
        $this->assertFalse($fileManager->read('foo.tmp'));
        $this->assertSame(
            $fileManager->getErrors(),
            array(
                'Unknown file: foo.tmp',
            )
        );
        $this->assertFalse($fileManager->delete('foo.tmp'));
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testReadAndDeleteFail(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertFalse($fileManager->readAndDelete('~/foo$/'));
        $this->assertFalse($fileManager->readAndDelete('foo'));
        $this->assertSame(
            $fileManager->getErrors(),
            array(
                'Unknown file: /foo$/',
                'Unknown file: foo',
            )
        );
        $this->assertTrue($fileManager->delete('foo.tmp'));
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testListContents(FileManager $fileManager)
    {
        $fileManager->deleteAll();
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->write('test/bar.tmp', 'bar'));

        $this->assertSame(count($fileManager->listContents()), 2);
        $this->assertSame(count($fileManager->listContents('test')), 1);
        $this->assertSame(count($fileManager->listContents('test/foo')), 0);

        $this->assertSame(count($fileManager->listContents('', true)), 3);
        $this->assertSame(count($fileManager->listContents('', true, FileManager::TYPE_DIR)), 1);
        $this->assertSame(count($fileManager->listContents('~/bar\.tmp$/', true, FileManager::TYPE_FILE)), 1);
        $this->assertSame(count($fileManager->listContents('~/bar\.tmp$/')), 0);
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testCreateDir(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->createDir('test'));
        $this->assertTrue($fileManager->has('test'));
        $this->assertTrue($fileManager->createDir('0'));
        $this->assertTrue($fileManager->has('0'));
        $this->assertTrue($fileManager->deleteDir('0'));
        $this->assertFalse($fileManager->has('0'));
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testVisibility(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertSame($fileManager->getVisibility('foo.tmp'), FileManager::VISIBILITY_PUBLIC);
        $fileManager->deleteAll();
        $this->assertTrue($fileManager->write('foo.tmp', 'foo', ['visibility' => FileManager::VISIBILITY_PRIVATE]));
        $this->assertSame($fileManager->getVisibility('foo.tmp'), FileManager::VISIBILITY_PRIVATE);
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testRenameSuccess(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->createDir('test'));
        $this->assertTrue($fileManager->rename('test', 'test_1'));
        $this->assertFalse($fileManager->has('test'));
        $this->assertTrue($fileManager->has('test_1'));
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testRenameFail(FileManager $fileManager)
    {
        $this->assertFalse($fileManager->rename('test', 'test_1'));
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testRenameByMask(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('test', ''));
        $this->assertTrue($fileManager->renameByMask('test', 'test_{num}', ['num' => 2]));
        $this->assertTrue($fileManager->has('test_2'));
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testCopy(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('test/foo.tmp', 'foo'));
        $this->assertTrue($fileManager->createDir('test_1'));
        $this->assertTrue($fileManager->copy('test/foo.tmp', 'test_1/foo.tmp'));
        $this->assertTrue($fileManager->has('test/foo.tmp'));
        $this->assertTrue($fileManager->has('test_1/foo.tmp'));
        $fileManager->deleteAll();
    }



    protected static function getFileManagerWithLocalCache()
    {
        return new FileManager(
            [
                'adapter' =>
                    function () {
                        return new Local(RUNTIME .'/filesystem');
                    },
                'cache' => function () {
                        $local = new Local(RUNTIME);
                        $cache = new Adapter($local, 'filesystem.tmp');

                        return $cache;
                    }
            ]
        );
    }
    public function providerFileManager()
    {
        return [
            [
                new FileManager([
                                    'adapter' =>
                                        function () {
                                            return new Local(RUNTIME .'/filesystem');
                                        }
                                ])
            ],
            [
                static::getFileManagerWithLocalCache()
            ],
            [
                new FileManager(
                    [
                        'adapter' =>
                            function () {
                                return new Local(RUNTIME .'/filesystem');
                            },
                        'cache' => function () {
                                $memcached = new \Memcached();
                                $memcached->addServer('localhost', 11211);

                                return new Memcached($memcached);
                            }
                    ]
                )
            ]
        ];
    }

    public static function tearDownAfterClass()
    {
        $memcached = new \Memcached();
        $memcached->addServer('localhost', 11211);
        $memcached->flush();
        static::getFileManagerWithLocalCache()->deleteAll();
        static::getFileManagerWithLocalCache()->flushCache();
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
 