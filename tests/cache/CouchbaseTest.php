<?php

namespace rockunit\cache;

use rock\cache\CacheInterface;
use rock\cache\Exception;
use rock\cache\Couchbase;
use rockunit\TestCase;

class CouchbaseTest extends TestCase
{
    public static function flush()
    {
        (new Couchbase(['enabled' => true]))->flush();
    }

    public function init($serialize)
    {
        return new Couchbase(['enabled' => true, 'serializer' => $serialize]);
    }

    /**
     * @dataProvider providerCache
     * @expectedException Exception
     */
    public function testGetAllKeys(CacheInterface $cache)
    {
        parent::testGetAllKeys($cache);
    }
} 