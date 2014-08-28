<?php

namespace rockunit\filemanager;


use League\Flysystem\Adapter\Local;
use League\Flysystem\Cache\Adapter;
use League\Flysystem\Cache\Memcached;
use rock\cache\filemanager\FileManager;

/**
 * @group base
 */
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
    public function testUpdate(FileManager $fileManager)
    {
        $fileManager->deleteAll();
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->update('foo.tmp', 'update'));
        $this->assertSame($fileManager->read('foo.tmp'), 'update');
        $this->assertFalse($fileManager->update('update.tmp', 'update'));
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
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testWriteFail(FileManager $fileManager)
    {
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertFalse($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->delete('foo.tmp'));
        $fileManager->deleteAll();
    }

//    /**
//     * @dataProvider providerFileManager
//     */
//    public function testWriteStream(FileManager $fileManager)
//    {
//        $fileManager->deleteAll();
//        $this->assertTrue($fileManager->write('test.tmp', 'foo'));
//        $this->assertTrue($fileManager->writeStream('baz.tmp', $fileManager->readStream('test.tmp')));
//        $this->assertTrue($fileManager->has('test.tmp'));
//        $this->assertTrue($fileManager->has('baz.tmp'));
//        $this->assertTrue($fileManager->writeStream('0', $fileManager->readStream('foo.tmp')));
//        $this->assertTrue($fileManager->has('foo.tmp'));
//        $this->assertTrue($fileManager->has('0'));
//
//        // repeat write fail
//        $this->assertFalse($fileManager->writeStream('0', $fileManager->readStream('foo.tmp')));
//        $fileManager->deleteAll();
//    }

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
        $fileManager->deleteAll();
    }

//    /**
//     * @dataProvider providerFileManager
//     */
//    public function testUpdateStream(FileManager $fileManager)
//    {
//        $fileManager->deleteAll();
//        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
//        $this->assertTrue($fileManager->write('bar.tmp', ''));
//        $this->assertTrue($fileManager->updateStream('bar.tmp', $fileManager->readStream('foo.tmp')));
//        $this->assertTrue($fileManager->has('foo.tmp'));
//        $this->assertTrue($fileManager->has('bar.tmp'));
//        $this->assertSame($fileManager->read('bar.tmp'), 'foo');
//        $this->assertTrue($fileManager->delete('bar.tmp'));
//        $this->assertTrue($fileManager->write('bar.tmp', '', FileManager::VISIBILITY_PRIVATE));
//        $this->assertFalse($fileManager->updateStream('baz.tmp', $fileManager->readStream('foo.tmp')));
//        $fileManager->deleteAll();
//    }

    /**
     * @dataProvider providerFileManager
     */
    public function testDeleteSuccess(FileManager $fileManager)
    {
        $fileManager->deleteAll();
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
        $fileManager->deleteAll();
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
        $fileManager->deleteAll();
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
        $fileManager->deleteAll();
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
        $fileManager->deleteAll();
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
    public function testListPaths(FileManager $fileManager)
    {
        $fileManager->deleteAll();
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->write('test/bar.tmp', 'bar'));

        $this->assertSame($this->sort($fileManager->listPaths()), $this->sort(['foo.tmp','test']));
        $this->assertSame($this->sort($fileManager->listPaths('', true)), $this->sort(['foo.tmp','test', 'test/bar.tmp']));
        $this->assertSame($fileManager->listPaths('', true, FileManager::TYPE_DIR), ['test']);
        $this->assertSame($this->sort($fileManager->listPaths('', true, FileManager::TYPE_FILE)), $this->sort(['foo.tmp', 'test/bar.tmp']));
        $this->assertSame(count($fileManager->listPaths('test')), 1);
        $this->assertSame(count($fileManager->listPaths('test/foo')), 0);
        $this->assertSame(count($fileManager->listPaths('~/bar\.tmp$/', true, FileManager::TYPE_FILE)), 1);
        $this->assertSame(count($fileManager->listPaths('~/bar\.tmp$/')), 0);
        $fileManager->deleteAll();
    }

    protected function sort($value)
    {
        sort($value);
        return $value;
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testListWith(FileManager $fileManager)
    {
        $fileManager->deleteAll();
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->write('test/bar.tmp', 'bar'));

        $this->assertArrayHasKey('timestamp', $fileManager->listWith([FileManager::META_TIMESTAMP])[0]);
        $this->assertSame(count($fileManager->listWith([FileManager::META_TIMESTAMP])), 2);
        $this->assertSame(count($fileManager->listWith([FileManager::META_TIMESTAMP], 'test')), 1);
        $this->assertSame(count($fileManager->listWith([FileManager::META_TIMESTAMP],'test/foo')), 0);

        $this->assertSame(count($fileManager->listWith([FileManager::META_TIMESTAMP],'', true)), 3);
        $this->assertSame(count($fileManager->listWith([FileManager::META_TIMESTAMP],'', true, FileManager::TYPE_DIR)), 1);
        $this->assertSame(count($fileManager->listWith([FileManager::META_TIMESTAMP],'~/bar\.tmp$/', true, FileManager::TYPE_FILE)), 1);
        $this->assertSame(count($fileManager->listWith([FileManager::META_TIMESTAMP],'~/bar\.tmp$/')), 0);
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testGetTimestamp(FileManager $fileManager)
    {
        $fileManager->deleteAll();
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->write('test/bar.tmp', 'bar'));
        $this->assertInternalType('int', $fileManager->getTimestamp('foo.tmp'));
        $this->assertSame(strlen($fileManager->getTimestamp('foo.tmp')), 10);
        $this->assertFalse($fileManager->getTimestamp('test/foo'));
        $this->assertInternalType('int', $fileManager->getTimestamp('~/bar\.tmp$/'));
        $this->assertSame(strlen($fileManager->getTimestamp('~/bar\.tmp$/')), 10);
        $this->assertFalse($fileManager->getTimestamp('~/baz\.tmp$/'));
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testGetMimetype(FileManager $fileManager)
    {
        $fileManager->deleteAll();
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->write('test/bar.tmp', 'bar'));
        $this->assertSame($fileManager->getMimetype('foo.tmp'), 'text/plain');
        $this->assertFalse($fileManager->getMimetype('test/foo'));
        $this->assertSame($fileManager->getMimetype('~/bar\.tmp$/'), 'text/plain');
        $this->assertFalse($fileManager->getMimetype('~/baz\.tmp$/'));
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testGetWithMetadata(FileManager $fileManager)
    {
        $fileManager->deleteAll();
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->write('test/bar.tmp', 'bar'));
        $this->assertSame($fileManager->getWithMetadata('foo.tmp', [FileManager::META_MIMETYPE])["mimetype"], 'text/plain');
        $this->assertFalse($fileManager->getWithMetadata('test/foo', [FileManager::META_MIMETYPE]));
        $this->assertSame($fileManager->getWithMetadata('~/bar\.tmp$/', [FileManager::META_MIMETYPE])["mimetype"], 'text/plain');
        $this->assertFalse($fileManager->getWithMetadata('~/baz\.tmp$/', [FileManager::META_MIMETYPE]));
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testGetMetadata(FileManager $fileManager)
    {
        $fileManager->deleteAll();
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->write('test/bar.tmp', 'bar'));
        $this->assertSame($fileManager->getMetadata('foo.tmp')["type"], 'file');
        $this->assertFalse($fileManager->getMetadata('test/foo'));
        $this->assertSame($fileManager->getMetadata('~/bar\.tmp$/')["type"], 'file');
        $this->assertFalse($fileManager->getMetadata('~/baz\.tmp$/'));
        $fileManager->deleteAll();
    }

    /**
     * @dataProvider providerFileManager
     */
    public function testGetSize(FileManager $fileManager)
    {
        $fileManager->deleteAll();
        $this->assertTrue($fileManager->write('foo.tmp', 'foo'));
        $this->assertTrue($fileManager->write('test/bar.tmp', 'bar'));
        $this->assertSame($fileManager->getSize('foo.tmp'), 3);
        $this->assertFalse($fileManager->getSize('test/foo'));
        $this->assertSame($fileManager->getSize('~/bar\.tmp$/'), 3);
        $this->assertFalse($fileManager->getSize('~/baz\.tmp$/'));
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
        $this->assertFalse($fileManager->getVisibility('baz.tmp'));
        $fileManager->deleteAll();
        $this->assertTrue($fileManager->write('foo.tmp', 'foo', ['visibility' => FileManager::VISIBILITY_PRIVATE]));
        $this->assertSame($fileManager->getVisibility('foo.tmp'), FileManager::VISIBILITY_PRIVATE);
        $this->assertSame($fileManager->getVisibility('~/foo\.tmp$/'), FileManager::VISIBILITY_PRIVATE);
        $this->assertFalse($fileManager->getVisibility('~/baz\.tmp$/'));
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
        $this->assertFalse($fileManager->renameByMask('test', 'test_{num}', ['num' => 2]));
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
        $this->assertFalse($fileManager->copy('test/foo.tmp', 'test_1/'));
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