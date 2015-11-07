<?php
namespace rock\cache;

use rock\base\BaseException;
use rock\events\EventsInterface;
use rock\helpers\Json;
use rock\log\Log;

/**
 * Memcached storage.
 *
 * if use expire "0", then time to live infinitely
 *
 * ```php
 * $cache = new Memcached;
 * $cache->set('key_1', 'foo', 0, ['tag_1']);
 * $cache->set('key_2', ['foo', 'bar'], 0, ['tag_1']);
 *
 * $cache->get('key_1'); //foo
 * $cache->get('key_2'); //['foo', 'bar']
 * ```
 *
 */
class Memcached implements CacheInterface, EventsInterface
{
    use CacheTrait {
        CacheTrait::setMulti as parentSetMulti;
    }

    public $servers = [
        ['host' => 'localhost', 'port' => 11211]
    ];
    /** @var  \Memcached */
    public $storage;

    public function init()
    {
        $this->parentInit();
        if (!$this->storage instanceof \Memcached) {
            $this->storage = new \Memcached();
            $this->normalizeServers($this->servers, $this->storage);
            $this->storage->setOption(\Memcached::OPT_COMPRESSION, true);

        }

        if ($this->serializer !== self::SERIALIZE_JSON) {
            $this->storage->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_PHP);
        }
    }

    /**
     * Returns current storage.
     * @return \Memcached
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return $this->unserialize($this->getInternal($key));
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value = null, $expire = 0, array $tags = [])
    {
        if (empty($key)) {
            return false;
        }
        $key = $this->prepareKey($key);
        $this->setTags($key, $tags);

        return $this->setInternal($key, $this->serialize($value), $expire);
    }

    /**
     * @inheritdoc
     */
    public function setMulti($values, $expire = 0, array $tags = [])
    {
        foreach ($values as $key => $value) {
            $key = $this->prepareKey($key);
            $this->setTags($key, $tags, $value);
            $values[$key] = $this->serialize($value);
        }
        $this->storage->setMulti($values, $expire);
    }

    /**
     * @inheritdoc
     */
    public function add($key, $value = null, $expire = 0, array $tags = [])
    {
        if (empty($key)) {
            return false;
        }

        if ($this->exists($key)) {
            return false;
        }

        return $this->set($key, $value, $expire, $tags);
    }

    /**
     * @inheritdoc
     */
    public function exists($key)
    {
        return (bool)$this->storage->get($this->prepareKey($key));
    }

    /**
     * @inheritdoc
     */
    public function touch($key, $expire = 0)
    {
        return $this->storage->touch($this->prepareKey($key), $expire);
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
            $this->storage->add($hash, 0, $expire);
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
            $this->storage->add($hash, 1, $expire);
        }

        return $this->storage->decrement($hash, $offset);
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        return $this->storage->delete($this->prepareKey($key));
    }

    /**
     * @inheritdoc
     */
    public function removeMulti(array $keys)
    {
        $keys = array_map(
            function ($value) {
                return $this->prepareKey($value);
            },
            $keys
        );
        $this->storage->deleteMulti($keys);
    }

    /**
     * @inheritdoc
     */
    public function getTag($tag)
    {
        return $this->unserialize($this->storage->get($this->prepareTag($tag)));
    }

    /**
     * @inheritdoc
     */
    public function existsTag($tag)
    {
        return (bool)$this->storage->get($this->prepareTag($tag));
    }

    /**
     * @inheritdoc
     */
    public function removeTag($tag)
    {
        $tag = $this->prepareTag($tag);
        if (!$value = $this->storage->get($tag)) {
            return false;
        }
        $value = $this->unserialize($value);
        $value[] = $tag;
        $this->storage->deleteMulti($value);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getAllKeys()
    {
        return $this->storage->getAllKeys();
    }

    /**
     * @inheritdoc
     */
    public function getAll()
    {
        return $this->storage->fetchAll();
    }

    /**
     * @inheritdoc
     */
    public function lock($key, $iteration = 15)
    {
        $i = 0;

        while (!$this->lockInternal($key)) {
            $i++;
            if ($i > $iteration) {
                if (class_exists('\rock\log\Log')) {
                    $message = BaseException::convertExceptionToString(new CacheException(CacheException::INVALID_SAVE, ['key' => $key]));
                    Log::err($message);
                }
                return false;
            }
            usleep(rand(10, 1000));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function unlock($key)
    {
        return $this->storage->delete($this->prepareKey($key, self::LOCK_PREFIX));
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        return $this->storage->flush();
    }

    /**
     * @inheritdoc
     */
    public function status()
    {
        return $this->storage->getStats();
    }

    /**
     * Sets a tags
     *
     * @param string $key key of cache
     * @param array $tags list of tags
     */
    protected function setTags($key, array $tags = [])
    {
        if (empty($tags)) {
            return;
        }

        foreach ($this->prepareTags($tags) as $tag) {
            if (($keys = $this->storage->get($tag)) !== false) {
                $keys = $this->unserialize($keys);
                if (in_array($key, $keys, true)) {
                    continue;
                }
                $keys[] = $key;
                $this->setInternal($tag, $this->serialize($keys), 0);
                continue;
            }
            $this->setInternal($tag, $this->serialize((array)$key), 0);
        }
    }

    protected function setInternal($key, $value, $expire)
    {
        return $this->storage->set($key, $value, $expire);
    }

    protected function serialize($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        if ($this->serializer & self::SERIALIZE_JSON) {
            return Json::encode($value);
        }

        return $value;
    }

    protected function lockInternal($key)
    {
        return $this->storage->add($this->prepareKey($key, self::LOCK_PREFIX), 1, $this->lockExpire);
    }

    /**
     * @param array $servers
     * @param \Memcached $storage
     */
    protected function normalizeServers(array $servers, $storage)
    {
        foreach ($servers as &$server) {
            $host = isset($server['host']) ? $server['host'] : 'localhost';
            $port = isset($server['port']) ? $server['port'] : 11211;
            $weight = isset($server['weight']) ? $server['weight'] : 0;
            $server = [$host, $port, $weight];
        }
        $storage->addServers($servers);
    }
}