<?php

namespace rockunit\core\cache\versioning;

use rock\cache\CacheInterface;
use rock\cache\versioning\Memcached;
use rockunit\CommonCache;

/**
 * @group cache
 * @group memcached
 */
class MemcachedTest extends CommonCache
{
    public function setUp()
    {
        if (!class_exists('\Memcached')) {
            $this->markTestSkipped(
                'The \Memcached is not available.'
            );
        }

        (new Memcached())->flush();
    }

    public function init($serialize, $lock)
    {
        if (!class_exists('\Memcached')) {
            $this->markTestSkipped(
                'The \Memcached is not available.'
            );
        }
        return new Memcached(['serializer' => $serialize, 'lock' => $lock]);
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
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $this->assertInternalType('string', $cache->getTag('foo'), 'var should be type string');
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
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));

        $timestamp = $cache->getTag('bar');
        $this->assertTrue($cache->removeTag('bar'), 'tag "bar" does not remove');
        $this->assertFalse($cache->get('key1'), 'should be get: false');
        $this->assertNotEquals($cache->getTag('bar'), $timestamp, 'timestamps does not equals');
        $this->assertTrue($cache->remove('key2'));
        $this->assertFalse($cache->get('key2'), 'should be get: false');
        $expected = $cache->getAllKeys();
        if ($expected !== false) {
            $actual = [CacheInterface::TAG_PREFIX . 'foo', CacheInterface::TAG_PREFIX . 'bar'];
            sort($expected);
            sort($actual);
            $this->assertEquals($expected, $actual, 'should be get: ' . json_encode($actual));
        }
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testStatus(CacheInterface $cache)
    {
        $this->markTestSkipped(
            'Memcached::status() skipped. Changed behavior TravisCI.'
        );
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