<?php

namespace rockunit;


use PHPUnit\Framework\TestCase;
use rock\cache\CacheInterface;

abstract class CommonCache extends TestCase
{
    abstract public function init($serialize);

    public function providerCache()
    {
        return [
            [$this->init(CacheInterface::SERIALIZE_PHP)],
            [$this->init(CacheInterface::SERIALIZE_JSON)],
        ];
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGet(CacheInterface $cache)
    {
        $this->assertFalse($cache->set('', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $this->assertEquals(
            ['one', 'two'],
            $cache->get('key1'),
            'should be get: ' . json_encode(['one', 'two'])
        );
        $this->assertEquals('three', $cache->get('key2'), 'should be get: "three"');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGetNotKey(CacheInterface $cache)
    {
        $this->assertFalse($cache->get('key3'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGetNull(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key5', null), 'should be get: true');
        $this->assertNull($cache->get('key5'), 'should be get: null');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testAddPrefix(CacheInterface $cache)
    {
        $cache->setPrefix('test');
        $this->assertTrue($cache->set('key5', ['foo']));
        $this->assertSame(['foo'], $cache->get('key5'));

        $cache->hashKey = 0;
        $this->assertTrue($cache->set('key6', ['foo']));
        $this->assertSame(['foo'], $cache->get('key6'));
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testKeySHA(CacheInterface $cache)
    {
        $cache->setPrefix('test');
        $cache->hashKey = CacheInterface::HASH_SHA;
        $this->assertTrue($cache->set('key5', ['foo']));
        $this->assertSame(['foo'], $cache->get('key5'));
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testSet(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key3'), 'should be get: true');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testSetFalse(CacheInterface $cache)
    {
        $this->assertFalse($cache->set(null), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testSetMulti(CacheInterface $cache)
    {
        $cache->setMulti(['foo' => 'text foo', 'bar' => 'text bar']);
        $this->assertEquals(['foo' => 'text foo', /*'baz' => false, */'bar' => 'text bar'], $cache->getMulti(['foo', 'baz', 'bar']));
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testAdd(CacheInterface $cache)
    {
        $this->assertFalse($cache->add(''));
        $this->assertTrue($cache->add('key3'), 'should be get: true');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testAddFalse(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertFalse($cache->add('key1'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testTtl(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key6', 'foo', 1), 'should be get: true');
        sleep(2);
        $this->assertFalse($cache->get('key6'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testTouch(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->touch('key1', 1), 'should be get: true');
        sleep(2);
        $this->assertFalse($cache->get('key1'), 'should be get: false');

        $this->assertTrue($cache->set('key3', ['one', 'two']));
        $this->assertTrue($cache->set('key4', ['foo', 'bar']));
        $this->assertTrue($cache->touch('key3', 1), 'should be get: true');
        $this->assertTrue($cache->touch('key4', 6), 'should be get: true');
        sleep(2);
        $this->assertFalse($cache->get('key3'), 'should be get: false');
        $this->assertSame($cache->get('key4'), ['foo', 'bar']);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testTouchMultiFalse(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two']));
        $this->assertTrue($cache->set('key2', ['foo', 'bar']));
        $this->assertTrue($cache->touchMulti(['key1','key2'], 1));
        sleep(3);
        $this->assertFalse($cache->get('key1'));
        $this->assertFalse($cache->get('key2'));

        $this->assertFalse($cache->touchMulti(['key1','key3'], 1));
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testTouchMultiTrue(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two']));
        $this->assertTrue($cache->set('key2', ['foo', 'bar']));
        $this->assertTrue($cache->touchMulti(['key1','key2'], 3));
        sleep(1);
        $this->assertSame($cache->get('key1'), ['one', 'two']);
        $this->assertSame($cache->get('key2'), ['foo', 'bar']);
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testTouchFalse(CacheInterface $cache)
    {
        $this->assertFalse($cache->touch('key6', 1), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testExistsTrue(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $this->assertTrue($cache->exists('key2'), 'should be get: true');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testExistsFalse(CacheInterface $cache)
    {
        $this->assertFalse($cache->exists('key9'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testExistsByTouchFalse(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->touch('key1', 1), 'should be get: true');
        sleep(2);
        $this->assertFalse($cache->exists('key1'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testExistsByRemoveFalse(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->remove('key1'), 'should be get: true');
        $this->assertFalse($cache->exists('key1'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testIncrement(CacheInterface $cache)
    {
        $this->assertEquals(5, $cache->increment('key7', 5), 'should be get: 5');
        $this->assertEquals(5, $cache->get('key7'), 'should be get: 5');

        $this->assertEquals(6, $cache->increment('key7'), 'should be get: 6');
        $this->assertEquals(6, $cache->get('key7'), 'should be get: 6');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testIncrementWithTtl(CacheInterface $cache)
    {
        $this->assertEquals(5,$cache->increment('key7', 5, 1), 'should be get: 5');
        sleep(3);
        $this->assertFalse($cache->get('key7'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testIncrementFalse(CacheInterface $cache)
    {
        $this->assertFalse($cache->increment('key7', 5, 0, false), 'should be get: false');
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

        $this->assertEquals(-2, $cache->decrement('key17', 2), 'should be get: -2');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testDecrementFalse(CacheInterface $cache)
    {
        $this->assertFalse($cache->decrement('key7', 5, 0, false), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testRemove(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->remove('key1'), 'should be get: true');
        $this->assertFalse($cache->get('key1'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testRemoveFalse(CacheInterface $cache)
    {
        $this->assertFalse($cache->remove('key3'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testRemoves(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $cache->removeMulti(['key2']);
        $this->assertTrue($cache->exists('key1'), 'should be get: true');
        $this->assertFalse($cache->get('key2'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGetTag(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $expected = $cache->getTag('foo');
        sort($expected);
        $actual = [$cache->prepareKey('key1'), $cache->prepareKey('key2')];
        sort($actual);
        $this->assertEquals($expected, $actual, 'should be get: ' . json_encode($actual));
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGetTags(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $this->assertEquals(
            ['bar', 'foo'],
            array_keys($cache->getMultiTags(['bar', 'foo'])),
            'should be get: ' . json_encode(['bar', 'foo'])
        );
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGetHashMd5Tags(CacheInterface $cache)
    {
        $cache->hashTag = CacheInterface::HASH_MD5;
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $this->assertEquals(
            array_keys($cache->getMultiTags(['bar', 'foo'])),
            ['bar', 'foo']
        );
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGetHashSHATags(CacheInterface $cache)
    {
        $cache->hashTag = CacheInterface::HASH_SHA;
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $this->assertEquals(
            array_keys($cache->getMultiTags(['bar', 'foo'])),
            ['bar', 'foo']
        );
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testExistsTag(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $this->assertTrue($cache->existsTag('foo'), 'should be get: true');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testExistsTagFalse(CacheInterface $cache)
    {
        $this->assertFalse($cache->existsTag('baz'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testRemoveTag(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $this->assertTrue($cache->removeTag('bar'), 'should be get: true');
        $this->assertFalse($cache->get('key1'), 'should be get: false');
        $this->assertFalse($cache->getTag('bar'), 'should be get tag: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testRemoveTagFalse(CacheInterface $cache)
    {
        $this->assertFalse($cache->removeTag('baz'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testRemoveMultiTags(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        $cache->removeMultiTags(['foo']);
        $this->assertFalse($cache->get('key1'), 'should be get: false');
        $this->assertFalse($cache->get('key2'), 'should be get: false');
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGetAllKeys(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']));
        $this->assertTrue($cache->set('key2', 'three', 0, ['foo']));
        sleep(3);
        $expected = $cache->getAllKeys();
        if ($expected !== false) {
            $actual = [
                $cache->prepareKey('key1'),
                $cache->prepareKey('key2'),
                CacheInterface::TAG_PREFIX . 'foo',
                CacheInterface::TAG_PREFIX . 'bar'
            ];
            sort($expected);
            sort($actual);
            $this->assertEquals($expected, $actual, 'should be get: ' . json_encode($actual));
        }
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testGetAll(CacheInterface $cache)
    {
        $this->assertTrue($cache->set('key5', 'foo'), 'should be get: true');
        $this->assertTrue($cache->set('key6', ['bar', 'baz']), 'should be get: true');
        $this->assertNotEmpty($cache->getAll());
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testLockAndUnlock(CacheInterface $cache)
    {
        $lock = $cache->lock('key1');
        $this->assertTrue($lock);
        if ($lock) {
            $this->assertTrue($cache->set('key1', ['one', 'two']));
        }
        $this->assertTrue($cache->unlock('key1'));
    }

    /**
     * @dataProvider providerCache
     * @param CacheInterface $cache
     */
    public function testStatus(CacheInterface $cache)
    {
        $this->assertNotEmpty($cache->status());
    }
}