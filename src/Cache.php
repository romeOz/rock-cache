<?php

namespace rock\cache;


use rock\events\EventsInterface;
use rock\events\EventsTrait;
use rock\helpers\Serialize;

abstract class Cache implements CacheInterface, EventsInterface
{
    use EventsTrait {
        EventsTrait::init as parentInit;
    }

    public $storage;
    /**
     * Prefix of key.
     * @var string
     */
    protected $prefix;
    /**
     * Enable hashing key.
     * @var int
     */
    protected $hashKey = self::HASH_MD5;
    /**
     * Enable hashing tag.
     * @var int
     */
    protected $hashTag = 0;
    /**
     * Serializer.
     * @var int
     */
    protected $serializer = self::SERIALIZE_PHP;
    /**
     * Time to live on lock (sec)
     * @var int
     */
    protected $lockExpire = 30;

    /**
     * Adds a prefix to key.
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = "{$prefix}_";
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHashKey($mode)
    {
        $this->hashKey = $mode;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHashTag($mode)
    {
        $this->hashTag = $mode;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setLockExpire($expire)
    {
        $this->lockExpire = $expire;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function prepareKey($key, $prefix = null)
    {
        if (!isset($prefix)) {
            $prefix = $this->prefix;
        }
        if ($this->hashKey & self::HASH_MD5) {
            return $prefix . md5($key);
        } elseif ($this->hashKey & self::HASH_SHA) {
            return $prefix . sha1($key);
        }
        return $prefix . $key;
    }

    /**
     * @inheritdoc
     */
    public function setMulti($values, $expire = 0, array $tags = [])
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $expire, $tags);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMulti(array $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            if (($value = $this->get($key)) !== false) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function touchMulti(array $keys, $expire = 0)
    {
        $result = true;
        foreach ($keys as $key) {
            if (!$this->touch($key, $expire)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function removeMulti(array $keys)
    {
        foreach ($keys as $key) {
            $this->remove($key);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMultiTags(array $tags)
    {
        $result = [];
        foreach ($tags as $tag) {
            if ($value = $this->getTag($tag)) {
                $result[$tag] = $value;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function removeMultiTags(array $tags)
    {
        foreach ($tags as $tag) {
            $this->removeTag($tag);
        }
    }

    /**
     * @param array $keys
     * @return string|null
     */
    protected function prepareKeys(array $keys = [])
    {
        if (empty($keys)) {
            return null;
        }

        return array_map(
            function ($value) {
                return $this->prepareKey($value);
            },
            $keys
        );
    }

    /**
     * @param array $tags tags
     * @return array|null
     */
    protected function prepareTags(array $tags = [])
    {
        if (empty($tags)) {
            return null;
        }
        $tags = array_unique($tags);
        sort($tags);

        return array_map(
            function ($value) {
                return $this->prepareTag($value);
            },
            $tags
        );
    }

    protected function prepareTag($tag)
    {
        if (empty($tag)) {
            return $tag;
        }

        if ($this->hashTag & self::HASH_MD5) {
            return self::TAG_PREFIX . md5($tag);
        } elseif ($this->hashTag & self::HASH_SHA) {
            return self::TAG_PREFIX . sha1($tag);
        }

        return self::TAG_PREFIX . $tag;
    }

    /**
     * Serialize value.
     * @param array $value
     * @return array|string
     */
    protected function serialize($value)
    {
        if (!is_array($value)) {
            return $value;
        }
        return Serialize::serialize($value, $this->serializer);
    }

    protected function getInternal($key)
    {
        if (empty($key)) {
            return false;
        }

        $key = $this->prepareKey($key);

        return $this->storage->get($key);
    }

    /**
     * Unserialize value.
     * @param $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        return Serialize::unserialize($value, false);
    }

    /**
     * Returns a microtime.
     * @param int|null $microtime
     * @return float
     */
    protected function microtime($microtime = null)
    {
        list($usec, $sec) = explode(" ", $microtime ?: microtime());
        return (float)$usec + (float)$sec;
    }
}