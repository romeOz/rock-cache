<?php
namespace rockunit\cache;

use rock\cache\CacheInterface;
use rock\cache\Exception;
use rock\cache\Memcache;

class MemcacheTest extends \PHPUnit_Framework_TestCase
{
    use  CommonTraitTest {
        CommonTraitTest::testGetAllKeys as parentTestGetAllKeys;
    }

    public static function flush()
    {
        (new Memcache(['enabled' => true]))->flush();
    }

    public function init($serialize)
    {
        return new Memcache(['enabled' => true, 'serializer' => $serialize]);
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
 