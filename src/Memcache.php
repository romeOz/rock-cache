<?php

namespace rock\cache;

class Memcache extends Memcached
{
    /** @var  \Memcache */
    protected  static $storage;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        static::$storage = new \Memcache();
        static::$storage->connect($this->host, $this->port);
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
        if (($value = static::$storage->get($tag)) === false) {
            return false;
        }
        $value = $this->unserialize($value);
        $value[] = $tag;
        foreach ($value as $key) {
            static::$storage->delete($key);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getAllKeys()
    {
        throw new Exception(Exception::UNKNOWN_METHOD, 0, ['method' => __METHOD__]);
    }

    /**
     * @inheritdoc
     */
    public function getAll()
    {
        throw new Exception(Exception::UNKNOWN_METHOD, 0, ['method' => __METHOD__]);
    }

    /**
     * Set lock of cache
     * "Dog-pile" ("cache miss storm") and "race condition" effects
     *
     * @param string $key - key of cache
     * @param mixed $value - content of cache
     * @param int   $expire - expire of cache
     * @param int   $count - iteration
     * @return bool
     */
    protected function provideLock($key, $value, $expire, &$count = 0)
    {
        if ($this->lock($key, $value)) {
            static::$storage->set($key, $value, MEMCACHE_COMPRESSED, $expire);
            $this->unlock($key);

            return true;
        }

        return false;
    }


    /**
     * Set lock
     * Note: Dog-pile" ("cache miss storm") and "race condition" effects
     *
     * @param string $key - key of cache
     * @param mixed $value - content of cache
     * @param int    $max - max iteration
     * @return bool
     */
    protected function lock($key, $value, $max = 15)
    {
        $iteration = 0;

        while (!static::$storage->add(self::LOCK_PREFIX . $key, $value, MEMCACHE_COMPRESSED, 5)) {
            $iteration++;
            if ($iteration > $max) {
                //throw new Exception( Exception::INVALID_SAVE, 0, ['key' => $key]);
                return false;
            }
            usleep(1000);
        }

        return true;
    }

    /**
     * Set dependency
     *
     * @param string $key - key of cache
     * @param array  $tags - list of tags
     */
    protected function setTags($key, array $tags = null)
    {
        if (empty($tags)) {
            return;
        }

        foreach ($this->prepareTags($tags) as $tag) {
            if (($value = static::$storage->get($tag)) !== false) {
                $value = $this->unserialize($value);
                if (in_array($key, $value, true)) {
                    continue;
                }
                $value[] = $key;
                $this->provideLock($tag, $this->serialize($value), 0);
                continue;
            }

            $this->provideLock($tag, $this->serialize((array)$key), 0);
        }
    }
}