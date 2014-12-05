<?php
namespace rockunit\cache;

use rock\cache\APC;
use rock\cache\CacheInterface;
use rock\cache\Exception;

/**
 * @group cache
 * @group apc
 */
class APCTest extends \PHPUnit_Framework_TestCase
{
    use  CommonTraitTest;

    public static function flush()
    {
        (new APC(['enabled' => true]))->flush();
    }

    public function init($serialize)
    {
        if (!extension_loaded('apc')) {
            $this->markTestSkipped(
                'The APC is not available.'
            );
        }
        $cache = new APC(['enabled' => true, 'serializer' => $serialize]);
        return $cache;
    }

    /**
     * @dataProvider providerCache
     * @expectedException Exception
     */
    public function testGetStorage(CacheInterface $cache)
    {
        $cache->getStorage();
    }

    /**
     * @dataProvider providerCache
     */
    public function testTtl(CacheInterface $cache)
    {
        $this->markTestSkipped('Skipping: ' . __METHOD__);
    }

    /**
     * @dataProvider providerCache
     */
    public function testExistsByTouchFalse(CacheInterface $cache)
    {
        $this->markTestSkipped('Skipping: ' . __METHOD__);
    }

    /**
     * @dataProvider providerCache
     */
    public function testTouch(CacheInterface $cache)
    {
        $this->markTestSkipped('Skipping: ' . __METHOD__);
    }

    /**
     * @dataProvider providerCache
     */
    public function testTouchMultiTrue(CacheInterface $cache)
    {
        $this->markTestSkipped('Skipping: ' . __METHOD__);
    }

    /**
     * @dataProvider providerCache
     */
    public function testTouchMultiFalse(CacheInterface $cache)
    {
        $this->markTestSkipped('Skipping: ' . __METHOD__);
    }

    /**
     * @dataProvider providerCache
     */
    public function testIncrementWithTtl(CacheInterface $cache)
    {
        $this->markTestSkipped('Skipping: ' . __METHOD__);
    }
}