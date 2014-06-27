<?php
namespace rockunit\cache;

use rock\cache\Memcached;

class MemcachedTest extends \PHPUnit_Framework_TestCase
{
    use  CommonTraitTest;

    public static function flush()
    {
        (new Memcached(['enabled' => true]))->flush();
    }

    public function init($serialize)
    {
        return new Memcached(['enabled' => true, 'serializer' => $serialize]);
    }
}
 