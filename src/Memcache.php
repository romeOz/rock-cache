<?php

namespace rock\cache;

use rock\log\Log;

class Memcache extends Memcached
{
    /** @var  \Memcache */
    public $storage;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->storage = new \Memcache();
        foreach ($this->servers as $server) {
            if (!isset($server[1])) {
                $server[1] = 11211;
            }
            if (!isset($server[2])) {
                $server[2] = true;
            }
            if (!isset($server[3])) {
                $server[3] = 1;
            }
            list($host, $port, $persistent, $weight) = $server;
            $this->storage->addserver($host, $port, $persistent, $weight);
        }
    }

    /**
     * @inheritdoc
     */
    public function setMulti($values, $expire = 0, array $tags = [])
    {
        $this->parentSetMulti($values, $expire, $tags);
    }

    /**
     * @inheritdoc
     */
    public function touch($key, $expire = 0)
    {
        if (($value = $this->get($key)) === false) {
            return false;
        }

        return $this->set($key, $value, $expire);
    }

    /**
     * @inheritdoc
     */
    public function increment($key, $offset = 1, $expire = 0, $create = true)
    {
        $hash = $this->prepareKey($key);
        if ($this->exists($key) === false) {
            if ($create === false) {
                return false;
            }
            $this->storage->add($hash, 0, MEMCACHE_COMPRESSED, $expire);
        }

        return $this->storage->increment($hash, $offset);
    }

    /**
     * @inheritdoc
     */
    public function decrement($key, $offset = 1, $expire = 0, $create = true)
    {
        $hash = $this->prepareKey($key);
        if ($this->exists($key) === false) {
            if ($create === false) {
                return false;
            }
            $this->storage->add($hash, 0, MEMCACHE_COMPRESSED, $expire);
        }

        return $this->storage->decrement($hash, $offset);
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
    public function removeTag($tag)
    {
        $tag = $this->prepareTag($tag);
        if (($value = $this->storage->get($tag)) === false) {
            return false;
        }
        $value = $this->unserialize($value);
        $value[] = $tag;
        foreach ($value as $key) {
            $this->storage->delete($key);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getAllKeys()
    {
        throw new CacheException(CacheException::UNKNOWN_METHOD, ['method' => __METHOD__]);
    }

    /**
     * @inheritdoc
     */
    public function getAll()
    {
        throw new CacheException(CacheException::UNKNOWN_METHOD, ['method' => __METHOD__]);
    }

    /**
     * @inheritdoc
     */
    protected function setInternal($key, $value, $expire)
    {
        return $this->storage->set($key, $value, MEMCACHE_COMPRESSED, $expire);
    }

    /**
     * @inheritdoc
     */
    protected function setTags($key, array $tags = [])
    {
        if (empty($tags)) {
            return;
        }

        foreach ($this->prepareTags($tags) as $tag) {
            if (($value = $this->storage->get($tag)) !== false) {
                $value = $this->unserialize($value);
                if (in_array($key, $value, true)) {
                    continue;
                }
                $value[] = $key;
                $this->setInternal($tag, $this->serialize($value), 0);
                continue;
            }

            $this->setInternal($tag, $this->serialize((array)$key), 0);
        }
    }

    /**
     * @inheritdoc
     */
    protected function lockInternal($key)
    {
        return $this->storage->add(self::LOCK_PREFIX . $key, 1, MEMCACHE_COMPRESSED, $this->lockExpire);
    }
}