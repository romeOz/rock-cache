<?php

namespace rock\cache\helpers;


class BaseDate 
{
    /**
     * Get microtime
     *
     * @param int|null $microtime
     * @return float
     */
    public static function microtime($microtime = null)
    {
        list($usec, $sec) = explode(" ", $microtime ? : microtime());
        return (float)$usec + (float)$sec;
    }
} 