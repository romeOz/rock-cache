<?php
namespace rockunit\cache;

use rock\cache\CacheInterface;
use rock\cache\Exception;
use rock\cache\Couchbase;

class CouchbaseTest extends \PHPUnit_Framework_TestCase
{
    use  CommonTraitTest {
        CommonTraitTest::testGetAllKeys as parentTestGetAllKeys;
    }

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
        $this->parentTestGetAllKeys($cache);
    }
} 