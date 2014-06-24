<?php

namespace rock\cache\helpers;


use rock\cache\Exception;

class BaseSerialize implements SerializeInterface
{
    /**
     * Serialize
     *
     * @param array $value
     * @param int   $serializer
     * @param int   $options - constants by JSON
     * @return array|string
     */
    public static function serialize(array $value, $serializer = self::SERIALIZE_PHP, $options = 0)
    {
        return $serializer === self::SERIALIZE_PHP ? serialize($value) : Json::encode($value, $options);
    }

    /**
     * @param mixed $value
     * @param bool  $throwException
     * @throws Exception
     * @return mixed
     */
    public static function unserialize($value, $throwException = true)
    {
        if ($throwException === false) {
            if (!is_string($value)) {
                return $value;
            }
        }

        if (static::is($value)) {
            return unserialize($value);
        } elseif (Json::is($value)) {
            return Json::decode($value);
        }
        if ($throwException == true) {
            throw new Exception('Value does not serialization.');
        }

        return $value;
    }

    /**
     * Validation is serialized
     *
     * @param string $value
     * @return bool
     */
    public static function is($value)
    {
        return is_string($value) && @unserialize($value);
    }
} 