<?php

namespace rockunit\cache;

use rock\cache\Couchbase;

trait CouchbaseTrait
{
    /** @var  Couchbase */
    protected $cache;

    public function testGet()
    {

        $this->assertEquals(
             $this->cache->get('key1'),
             ['one', 'two'],
             'should be get: ' . json_encode(['one', 'two'])
        );
        $this->assertEquals($this->cache->get('key2'), 'three', 'should be get: "three"');
    }

    public function testGetNotKey()
    {

        $this->assertFalse($this->cache->get('key3'), 'should be get: false');
    }

    public function testGetNull()
    {
        $this->assertTrue($this->cache->set('key5', null), 'should be get: true');
        $this->assertNull($this->cache->get('key5'), 'should be get: null');
    }

    public function testSet()
    {
        $this->assertTrue($this->cache->set('key3'), 'should be get: true');
    }

    public function testSetFalse()
    {
        $this->assertFalse($this->cache->set(null), 'should be get: false');
    }


    public function testAdd()
    {
        $this->assertTrue($this->cache->add('key3'), 'should be get: true');
    }

    public function testAddFalse()
    {
        $this->assertFalse($this->cache->add('key1'), 'should be get: false');
    }

    public function testTtl()
    {
        $this->assertTrue($this->cache->set('key6', 'foo', 1), 'should be get: true');
        sleep(2);
        $this->assertFalse($this->cache->get('key6'), 'should be get: false');
    }

    public function testTouch()
    {
        $this->assertTrue($this->cache->touch('key1', 1), 'should be get: true');
        sleep(2);
        $this->assertFalse($this->cache->get('key1'), 'should be get: false');
    }

    public function testTouchFalse()
    {
        $this->assertFalse($this->cache->touch('key6', 1), 'should be get: false');
    }

    public function testHas()
    {
        $this->assertTrue($this->cache->has('key2'), 'should be get: true');
    }

    public function testHasFalse()
    {
        $this->assertFalse($this->cache->has('key9'), 'should be get: false');
    }


    public function testIncrement()
    {
        $this->assertEquals($this->cache->increment('key7', 5), 5, 'should be get: 5');
        $this->assertEquals($this->cache->get('key7'), 5, 'should be get: 5');
    }

    public function testTtlIncrement()
    {
        $this->assertEquals($this->cache->increment('key7', 5, 1), 5, 'should be get: 5');
        sleep(2);
        $this->assertFalse($this->cache->get('key7'), 'should be get: false');
    }

    public function testDecrementFalse()
    {
        $this->assertFalse($this->cache->decrement('key7', 5), 'should be get: false');
    }

    public function testDecrement()
    {
        $this->assertEquals($this->cache->increment('key7', 5), 5, 'should be get: 5');
        $this->assertEquals($this->cache->decrement('key7', 2), 3, 'should be get: 3');
        $this->assertEquals($this->cache->get('key7'), 3, 'should be get: 3');
    }


    public function testRemove()
    {
        $this->assertTrue($this->cache->remove('key1'), 'should be get: true');
        $this->assertFalse($this->cache->get('key1'), 'should be get: false');
    }

    public function testRemoveFalse()
    {
        $this->assertFalse($this->cache->remove('key3'), 'should be get: false');
    }

    public function testRemoves()
    {
        $this->cache->removeMulti(['key2']);
        $this->assertFalse($this->cache->get('key2'), 'should be get: false');
    }

    public function testGetTag()
    {
        $expected = $this->cache->getTag('foo');
        sort($expected);
        $actual = [$this->cache->prepareKey('key1'), $this->cache->prepareKey('key2')];
        sort($actual);
        $this->assertEquals($expected, $actual, 'should be get: ' . json_encode($actual));
    }

    public function testGetMultiTags()
    {
        $this->assertEquals(array_keys($this->cache->getMultiTags(['bar', 'foo'])), ['bar', 'foo'], 'should be get: ' . json_encode(['bar', 'foo']));
    }

    public function testHasTag()
    {
        $this->assertTrue($this->cache->hasTag('foo'), 'should be get: true');
    }

    public function testHasTagFalse()
    {
        $this->assertFalse($this->cache->hasTag('baz'), 'should be get: false');
    }


    public function testRemoveTag()
    {
        $this->assertTrue($this->cache->removeTag('bar'), 'should be get: true');
        $this->assertFalse($this->cache->get('key1'), 'should be get: false');
        $this->assertFalse($this->cache->getTag('bar'), 'should be get tag: false');
    }
    public function testRemoveTagFalse()
    {
        $this->assertFalse($this->cache->removeTag('baz'), 'should be get: false');
    }

    public function testRemoveMultiTags()
    {
        $this->cache->removeMultiTags(['foo']);
        $this->assertFalse($this->cache->get('key1'), 'should be get: false');
        $this->assertFalse($this->cache->get('key2'), 'should be get: false');
    }
} 