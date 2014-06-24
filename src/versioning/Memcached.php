<?php
namespace rock\cache\versioning;

use rock\cache\CacheInterface;
use rock\cache\CacheTrait;
use rock\cache\helpers\Date;

class Memcached extends \rock\cache\Memcached implements CacheInterface
{
    use VersioningTrait;

    /** @var  \Memcached */
    protected static $storage;

    /**
     * @inheritdoc
     */
    public function getTag($tag)
    {
        return static::$storage->get(self::TAG_PREFIX . $tag);
    }

    /**
     * @inheritdoc
     */
    public function removeTag($tag)
    {
        return static::$storage->replace(self::TAG_PREFIX . $tag, microtime(), 0);
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
