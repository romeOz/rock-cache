<?php
namespace rockunit\cache;

use rock\cache\APC;
use rock\cache\CacheInterface;

class APCTest extends \PHPUnit_Framework_TestCase
{
    use  CommonTraitTest;

    public static function flush()
    {
        (new APC(['enabled' => true]))->flush();
    }

    public function init($serialize)
    {
        $cache = new APC(['enabled' => true, 'serializer' => $serialize]);
        return $cache;
    }

    /**
     * @dataProvider providerCache
     */
    public function testTtl(CacheInterface $cache)
    {

    }

    /**
     * @dataProvider providerCache
     */
    public function testHasByTouchFalse(CacheInterface $cache)
    {

    }

    /**
     * @dataProvider providerCache
     */
    public function testTouch(CacheInterface $cache)
    {

    }

    /**
     * @dataProvider providerCache
     */
    public function testTouchMultiTrue(CacheInterface $cache)
    {

    }

    /**
     * @dataProvider providerCache
     */
    public function testTouchMultiFalse(CacheInterface $cache)
    {

    }

    /**
     * @dataProvider providerCache
     */
    public function testIncrementWithTtl(CacheInterface $cache)
    {

    }
}
 