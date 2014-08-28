<?php

namespace rockunit;


use rock\cache\helpers\String;

/**
 * @group base
 * @group helpers
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerValue
     */
    public function testReplace($value, array $dataReplace, $result)
    {
        $this->assertSame(String::replace($value, $dataReplace), $result);
    }

    public function providerValue()
    {
        return [
            [['foo'], [], ['foo']],
            ['', [], ''],
            ['hello {value} !!!', ['value'=> 'world'], 'hello world !!!'],
            ['hello {value} !!!', [], 'hello {value} !!!'],
        ];
    }
}