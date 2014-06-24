<?php

namespace rock\cache;

use rock\cache\helpers\Serialize;

trait CommonTrait
{
    use ObjectTrait;

    /**
     * Prefix of key
     * @var string
     */
    public $prefix;

    /**
     * @var int
     */
    public $hashKey = self::HASH_MD5;
    public $hashTag = 0;
    /**
     * Serializer
     * @var int
     */
    public $serializer = self::SERIALIZE_PHP;

    /**
     * Enabled cache
     *
     * @var bool
     */
    public $enabled = false;

    /**
     * Enabled caching
     */
    public function enabled()
    {
        $this->enabled = true;
    }

    /**
     * Disabled caching
     */
    public function disabled()
    {
        $this->enabled = false;
    }

    /**
     * Add prefix to key
     * @param string $prefix
     */
    public function addPrefix($prefix)
    {
        $this->prefix = "{$prefix}_";
    }

    /**
     * Get prepare key of cache
     *
     * @param string $key
     * @return string
     */
    public function prepareKey($key)
    {
        if ($this->hashKey & self::HASH_MD5) {
            return $this->prefix . md5($key);
        } elseif ($this->hashKey & self::HASH_SHA) {
            return $this->prefix . sha1($key);
        }
        return $this->prefix . $key;
    }


    /**
     * @param array $tags - tags
     * @return string|null
     */
    protected function prepareTags(array $tags = null)
    {
        if (empty($tags)) {
            return null;
        }
        $tags = array_unique($tags);
        sort($tags);

        if ($this->hashTag & self::HASH_MD5) {
            return array_map(function($value){
                    return md5($value);
                }, $tags);
        } elseif ($this->hashTag & self::HASH_SHA) {
            return array_map(function($value){
                    return sha1($value);
                }, $tags);
        }

        return $tags;
    }

    /**
     * Serialize value
     *
     * @param array $value
     * @return array|string
     */
    protected function serialize(array $value)
    {
        return Serialize::serialize($value, $this->serializer);
    }

    /**
     * Unserialize value
     *
     * @param $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        return Serialize::unserialize($value, false);
    }

    /**
     * @inheritdoc
     */
    public function setMulti($values, $expire = 0, array $tags = null)
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
            $result[$key] = $this->get($key);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function touchMulti(array $keys, $expire = 0)
    {
        foreach ($keys as $key) {
            $this->touch($key, $expire);
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
} 