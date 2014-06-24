<?php

namespace rock\cache\helpers;


class BaseString 
{
    /**
     * Replace
     *
     * @param string $string       - string
     * @param array  $dataReplace - array replace
     * @return string
     */
    public static function replace($string, array $dataReplace = [])
    {
        if (is_array($string) || empty($dataReplace)) {
            return $string;
        }
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($dataReplace as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        $string = strtr($string, $replace);
        // interpolate replacement values into the message and return
        return $string;
    }
} 