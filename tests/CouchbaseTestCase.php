<?php

namespace rockunit;


class CouchbaseTestCase
{
    public static  function run($object = null)
    {
        if (!isset($object)) {
            $object = new static;
        }
        foreach (static::getMethodsChild(get_class($object)) as $method) {
            $object->setUp()->$method();
        }
    }

    public static function getMethodsChild($childClass, $excludeMethods = ['setUp'])
    {
        $child  = new \ReflectionClass($childClass);

        $parentClass = $child->getParentClass()->getName();
        $childMethods = $child->getMethods(\ReflectionMethod::IS_PUBLIC ^ \ReflectionMethod::IS_PROTECTED);

        $result = [];
        foreach($childMethods as $childMethod) {

            if ($childMethod->class === $parentClass) {
                continue;
            }
            $result[] = $childMethod->getName();

        }

        return array_diff_key($result, $excludeMethods);
    }

    private function _trace()
    {
        $trace = debug_backtrace(-2)[2];
        $trace['function'] = "{$trace['class']}::{$trace['function']}";
        return implode(' ', [$trace['function'], $trace['file'], $trace['line']]);
    }

    public function assertEquals($expected, $actual, $msg = null)
    {
        if ($expected === $actual) {
            var_dump(true);
            return;
        }

        var_dump($msg . '   =>  '. $this->_trace());
    }

    public function assertNotEquals($expected, $actual, $msg = null)
    {
        if ($expected !== $actual) {
            var_dump(true);
            return;
        }

        var_dump($msg . '   =>  '. $this->_trace());
    }

    public function assertTrue($expected, $msg = null)
    {
        if ($expected === true) {
            var_dump(true);
            return;
        }

        var_dump($msg . '   =>  '. $this->_trace());
    }

    public function assertFalse($expected, $msg = null)
    {
        if ($expected === false) {
            var_dump(true);
            return;
        }

        var_dump($msg . '   =>  '. $this->_trace());
    }

    public function assertNull($expected, $msg = null)
    {
        if ($expected === null) {
            var_dump(true);
            return;
        }

        var_dump($msg . '   =>  '. $this->_trace());
    }

    public function assertInternalType($type, $expected, $msg = null)
    {
        if (gettype($expected) === $type) {
            var_dump(true);
            return;
        }

        var_dump($msg . '   =>  '. $this->_trace());
    }
}