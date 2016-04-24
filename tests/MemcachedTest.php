<?php
namespace rockunit;

use rock\cache\CacheInterface;
use rock\cache\Memcached;

/**
 * @group cache
 * @group memcached
 */
class MemcachedTest extends CommonCache
{
    public function getStorage(array $config = [])
    {
        $config['servers'] = [[
            'host' => 'memcached',
            'port' => 11211
        ]];

        return new Memcached($config);
    }

    public function setUp()
    {
        if (!class_exists('\Memcached')) {
            $this->markTestSkipped(
                'The \Memcached is not available.'
            );

        }

        $this->getStorage()->flush();
    }

    public function init($serialize)
    {
        if (!class_exists('\Memcached')) {
            $this->markTestSkipped(
                'The \Memcached is not available.'
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
        $this->assertTrue($cache->getStorage() instanceof \Memcached);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGetAll(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key5', 'foo'), 'should be get: true');
        $this->assertTrue($cache->set('key6', ['bar', 'baz']), 'should be get: true');
        $this->assertEmpty($cache->getAll());
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testStatus(CacheInterface $cache)
    {

        $this->assertNotEmpty($cache->status());
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testDecrement(CacheInterface $cache)
    {
        $this->assertEquals(5, $cache->increment('key7', 5), 'should be get: 5');
        $this->assertEquals(3, $cache->decrement('key7', 2), 'should be get: 3');
        $this->assertEquals(3, $cache->get('key7'), 'should be get: 3');

        $this->assertEquals(0, $cache->decrement('key17', 2), 'should be get: 0');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testTouch(CacheInterface $cache)
    {
        if(defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                '\Memcached::touch() does not seem to support HHVM right now.'
            );
        }

        parent::testTouch($cache);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testTouchMultiFalse(CacheInterface $cache)
    {
        if(defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                '\Memcached::touch() does not seem to support HHVM right now.'
            );
        }

        parent::testTouchMultiFalse($cache);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testTouchMultiTrue(CacheInterface $cache)
    {
        if(defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                '\Memcached::touch() does not seem to support HHVM right now.'
            );
        }

        parent::testTouchMultiTrue($cache);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testTouchFalse(CacheInterface $cache)
    {
        if(defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                '\Memcached::touch() does not seem to support HHVM right now.'
            );
        }

        parent::testTouchFalse($cache);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testExistsByTouchFalse(CacheInterface $cache)
    {
        if(defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                '\Memcached::touch() does not seem to support HHVM right now.'
            );
        }

        parent::testExistsByTouchFalse($cache);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testRemoves(CacheInterface $cache)
    {
        if(defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                '\Memcached::deleteMulti() does not seem to support HHVM right now.'
            );
        }
        parent::testRemoves($cache);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testRemoveTag(CacheInterface $cache)
    {
        if(defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                '\Memcached::deleteMulti() does not seem to support HHVM right now.'
            );
        }

        parent::testRemoveTag($cache);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testRemoveTagFalse(CacheInterface $cache)
    {
        if(defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                '\Memcached::deleteMulti() does not seem to support HHVM right now.'
            );
        }
        parent::testRemoveTagFalse($cache);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testRemoveMultiTags(CacheInterface $cache)
    {
        if(defined('HHVM_VERSION')) {
            $this->markTestSkipped(
                '\Memcached::deleteMulti() does not seem to support HHVM right now.'
            );
        }
        parent::testRemoveMultiTags($cache);
    }
}