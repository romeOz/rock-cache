<?php

namespace rock\cache\versioning;

use rock\cache\CacheInterface;
use rock\cache\helpers\Date;

class Couchbase extends \rock\cache\Couchbase implements CacheInterface
{
    use VersioningTrait;

    /** @var  \Couchbase */
    protected static $storage;


    /**
     * @inheritdoc
     */
    public function getTag($tag)
    {
        return static::$storage->get($this->prepareTag($tag));
    }

    /**
     * @inheritdoc
     */
    public function removeTag($tag)
    {
        return is_string(static::$storage->replace($this->prepareTag($tag), microtime(), 0));
    }


    protected function validTimestamp($key, array $tagsByValue = null)
    {
        if (empty($tagsByValue)) {
            return true;
        }
        $tags = static::$storage->getMulti(array_keys($tagsByValue));
        foreach ($tagsByValue as $tag => $timestamp) {
            if (!isset($tags[$tag]) ||
                (isset($tags[$tag]) && Date::microtime($tags[$tag]) > Date::microtime($timestamp))
            ) {
                static::$storage->delete($key);

                return false;
            }
        }

        return true;
    }
}