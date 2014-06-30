<?php

namespace rockunit;


use rock\cache\helpers\Date;

class DateTest extends \PHPUnit_Framework_TestCase
{
    public function testMicrotime()
    {
        $this->assertInternalType('float', Date::microtime());
        $this->assertInternalType('float', Date::microtime(microtime()));
    }
}
 