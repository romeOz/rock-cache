<?php

namespace rockunit\cache;

use rock\cache\Couchbase;
use rockunit\CouchbaseTestCase;

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
} 