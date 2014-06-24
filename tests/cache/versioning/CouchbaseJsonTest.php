<?php

namespace rockunit\cache\versioning;

use rock\cache\versioning\Couchbase;
use rock\tests\cache\CouchbaseTrait;
use rock\tests\CouchbaseTestCase;

class CouchbaseJsonTest extends CouchbaseTestCase
{
    use CouchbaseTrait;

    public function setUp()
    {
        $this->cache = new Couchbase(['enabled' => true, 'serializer' => Couchbase::SERIALIZE_JSON]);
        $this->cache->flush();
        $this->cache->set('key1', ['one', 'two'], 0, ['foo', 'bar']);
        $this->cache->set('key2', 'three', 0, ['foo']);
        return $this;
    }


    public function testHasFalseByTouch()
    {
        $this->assertTrue($this->cache->touch('key1', 1), 'should be get: true');
        sleep(2);
        $this->assertFalse($this->cache->has('key1'), 'should be get: false');
    }

    public function testHasFalseByRemove()
    {
        $this->assertTrue($this->cache->remove('key1'), 'should be get: true');
        $this->assertFalse($this->cache->has('key1'), 'should be get: false');
    }



    public function testTtlDecrement()
    {
        $this->assertEquals($this->cache->increment('key7', 5), 5, 'should be get: 5');
        $this->assertEquals($this->cache->decrement('key7', 2, 1), 3, 'should be get: 3');
        sleep(2);
        $this->assertFalse($this->cache->get('key7'), 'should be get: false');
    }
    public function testHasTtlDecrement()
    {
        $this->assertEquals($this->cache->increment('key7', 5), 5, 'should be get: 5');
        $this->assertEquals($this->cache->decrement('key7', 2, 1), 3, 'should be get: 3');
        sleep(2);
        $this->assertFalse($this->cache->has('key7'), 'should be get: false');
    }


    public function testGetTag()
    {
        $this->assertInternalType('string', $this->cache->getTag('foo'), 'var should be type string');
    }

    public function testRemoveTag()
    {
        $timestamp = $this->cache->getTag('bar');
        $this->assertTrue($this->cache->removeTag('bar'), 'tag "bar" does not remove');
        $this->assertFalse($this->cache->get('key1'), 'should be get: false');
        $this->assertNotEquals($this->cache->getTag('bar'), $timestamp, 'timestamps does not equals');
    }
} 