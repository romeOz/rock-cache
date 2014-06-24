<?php

namespace rock\cache\helpers;


class BaseArrayHelper 
{
    /**
     * Returns the values of a specified column in an array.
     * The input array should be multidimensional or an array of objects.
     *
     * For example,
     *
     * ```php
     * $array = [
     *     ['id' => '123', 'data' => 'abc'],
     *     ['id' => '345', 'data' => 'def'],
     * ];
     * $result = ArrayHelper::getColumn($array, 'id');
     * // the result is: ['123', '345']
     *
     * // using anonymous function
     * $result = ArrayHelper::getColumn($array, function ($element) {
     *     return $element['id'];
     * });
     * ```
     *
     * @param array           $array
     * @param \Closure|string $name
     * @param boolean         $keepKeys whether to maintain the array keys. If false, the resulting array
     *                                  will be re-indexed with integers.
     * @return array the list of column values
     */
    public static function getColumn($array, $name, $keepKeys = true)
    {
        $result = [];
        if ($keepKeys) {
            foreach ($array as $k => $element) {
                $result[$k] = static::getValue($element, $name);
            }
        } else {
            foreach ($array as $element) {
                $result[] = static::getValue($element, $name);
            }
        }

        return $result;
    }


    /**
     * Retrieves the value of an array element or object property with the given key or property name.
     * If the key does not exist in the array or object, the default value will be returned instead.
     *
     * The key may be specified in a dot format to retrieve the value of a sub-array or the property
     * of an embedded object. In particular, if the key is `x.y.z`, then the returned value would
     * be `$array['x']['y']['z']` or `$array->x->y->z` (if `$array` is an object). If `$array['x']`
     * or `$array->x` is neither an array nor an object, the default value will be returned.
     * Note that if the array already has an element `x.y.z`, then its value will be returned
     * instead of going through the sub-arrays.
     *
     * Below are some usage examples,
     *
     * ```php
     * // working with array
     * $username = ArrayHelper::getValue($_POST, 'username');
     * // working with object
     * $username = ArrayHelper::getValue($user, 'username');
     * // working with anonymous function
     * $fullName = ArrayHelper::getValue($user, function($user, $defaultValue) {
     *     return $user->firstName . ' ' . $user->lastName;
     * });
     * // using dot format to retrieve the property of embedded object
     * $street = ArrayHelper::get($users, 'address.street');
     * ```
     *
     * @param array|object          $array   array or object to extract value from
     * @param string|array|\Closure $key     key name of the array element, or property name of the object,
     *                                       or an anonymous function returning the value. The anonymous function signature should be:
     *                                       `function($array, $defaultValue)`.
     * @param mixed                 $default the default value to be returned if the specified key does not exist
     * @return mixed the value of the element if found, default value otherwise
     */
    public static function getValue($array, $key, $default = null)
    {
        if (empty($array)) {
            return $default;
        }
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }
        if (is_array($array) && is_array($key)) {
            return static::keyAsArray($array, $key, $default);
        }
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }
        if (($pos = strrpos($key, '.')) !== false) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }
        if (is_object($array)) {
            return $array->$key;
        } elseif (is_array($array)) {
            return array_key_exists($key, $array) ? $array[$key] : $default;
        } else {
            return $default;
        }
    }

    /**
     * @param array $keys  - keys
     * @param array $array - current array
     * @param mixed $default
     * @return mixed
     */
    protected static function keyAsArray(array $array, array $keys, $default = null)
    {
        if (!$keys) {
            return $array;
        }
        $current = $array;
        foreach ($keys as $key) {
            if (!is_array($current) || empty($current[$key])) {
                return $default;
            }
            $current = $current[$key];
        }

        return $current;
    }
} 