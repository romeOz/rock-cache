<?php

namespace rock\cache;

use rock\base\BaseException;
use rock\events\EventsInterface;
use rock\log\Log;

class Couchbase implements CacheInterface, EventsInterface
{
    use CacheTrait {
        CacheTrait::__construct as parentConstruct;
        CacheTrait::setMulti as parentSetMulti;
    }

    /**
     * @var string
     */
    public $host = '127.0.0.1:8091'; // or http://10.4.4.1,10.4.4.2,10.4.4.3
    /** @var  string */
    public $username = '';
    /** @var  string */
    public $password = '';
    /** @var string */
    public $bucket = 'default';

    /** @var  \CouchbaseBucket */
    public $storage;

    public function __construct(array $config = [])
    {
        $this->parentConstruct($config);
        $this->storage = (new \CouchbaseCluster($this->host, $this->username, $this->password))->openBucket($this->bucket);
        switch ($this->serializer) {
            case self::SERIALIZE_JSON:
                $encode = 'couchbase_default_encoder';
                $decode = function ($bytes, $flags, $datatype) {
                    $options = [
                        'jsonassoc' => true
                    ];
                    return couchbase_basic_decoder_v1($bytes, $flags, $datatype, $options);
                };
                break;
            default:
                $encode = function ($value) {
                    $options = [
                        'sertype' => COUCHBASE_SERTYPE_PHP,
                        'cmprtype' => COUCHBASE_CMPRTYPE_NONE,
                        'cmprthresh' => 2000,
                        'cmprfactor' => 1.3
                    ];
                    return couchbase_basic_encoder_v1($value, $options);
                };
                $decode = 'couchbase_default_decoder';
        }

        $this->storage->setTranscoder($encode, $decode);
    }

    /**
     * @inheritdoc
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
        if (empty($key)) {
            return false;
        }

        $key = $this->prepareKey($key);
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
            $this->storage->insert($key, $values[$key], ['expiry' => $expire]);
        }
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
    public function touch($key, $expire = 0)
    {
        return $this->touchInternal($this->prepareKey($key), $expire);
    }

    /**
     * @inheritdoc
     */
    public function exists($key)
    {
        $key = $this->prepareKey($key);
        return $this->existsInternal($key);
    }

    /**
     * @inheritdoc
     */
    public function increment($key, $offset = 1, $expire = 0, $create = true)
    {
        $hash = $this->prepareKey($key);
        if (!$create && $this->exists($key) === false) {
            return false;
        }

        return $this->storage->counter($hash, $offset, ['expiry' => $expire, 'initial' => $offset])->value;
    }

    /**
     * @inheritdoc
     */
    public function decrement($key, $offset = 1, $expire = 0, $create = true)
    {
        $hash = $this->prepareKey($key);
        if (!$create && $this->exists($key) === false) {
            return false;
        }

        return $this->storage->counter($hash, $offset * -1, ['expiry' => $expire, 'initial' => 0])->value;
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        return $this->removeInternal($this->prepareKey($key));
    }

    /**
     * @inheritdoc
     */
    public function getTag($tag)
    {
        return $this->unserialize($this->getInternal($this->prepareTag($tag)));
    }

    /**
     * @inheritdoc
     */
    public function existsTag($tag)
    {
        $tag = $this->prepareTag($tag);
        return $this->existsInternal($tag);
    }

    /**
     * @inheritdoc
     */
    public function removeTag($tag)
    {
        $tag = $this->prepareTag($tag);
        if (!$value = $this->getInternal($tag)) {
            return false;
        }
        $keys = $this->unserialize($value);
        $keys[] = $tag;
        $this->storage->remove($keys);

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
    public function lock($key, $iteration = 15)
    {
        $i = 0;

        while (!(bool)$this->existsAndUpsert($this->prepareKey($key, self::LOCK_PREFIX), 1, $this->lockExpire)) {
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
        return $this->removeInternal($this->prepareKey($key, self::LOCK_PREFIX));
    }

    /**
     * {@inheritdoc}
     * >Note: might need to be enabled for a specific bucket.
     */
    public function flush()
    {
        return $this->storage->manager()->flush();
    }

    /**
     * @inheritdoc
     */
    public function status()
    {
        return $this->storage->manager()->info();
    }

    protected function setTags($key, array $tags = [])
    {
        if (empty($tags)) {
            return;
        }

        foreach ($this->prepareTags($tags) as $tag) {
            if ($keys = $this->getInternal($tag)) {
                $keys = $this->unserialize($keys);
                if (is_object($keys)) {
                    $keys = (array)$keys;
                }
                if (in_array($key, $keys, true)) {
                    continue;
                }
                $keys[] = $key;
                $this->setInternal($tag, $this->serialize($keys), 0, true);
                continue;
            }
            $this->setInternal($tag, $this->serialize((array)$key), 0);
        }
    }

    protected function setInternal($key, $value, $expire, $upsert = false)
    {
        if ($upsert) {
            $this->storage->upsert($key, $value, ['expiry' => $expire]);
        } else {
            $this->storage->insert($key, $value, ['expiry' => $expire]);
        }
        return true;
    }

    private function existsAndUpsert($key, $value, $expire = 0)
    {
        if ($this->existsInternal($key)) {
            return false;
        }
        $this->storage->upsert($key, $value, ['expiry' => $expire]);
        return true;
    }

    protected function getInternal($key)
    {
        try {
            return $this->storage->get($key)->value;
        } catch (\CouchbaseException $e) {
            return false;
        }
    }

    protected function existsInternal($key)
    {
        try {
            $this->storage->get($key);
            return true;
        } catch (\CouchbaseException $e) {
            return false;
        }
    }

    protected function touchInternal($key, $expire = 0)
    {
        try {
            $this->storage->touch($key, $expire);
            return true;
        } catch (\CouchbaseException $e) {
            return false;
        }
    }

    protected function removeInternal($key)
    {
        try {
            $this->storage->remove($key);
            return true;
        } catch (\CouchbaseException $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    protected function serialize($value)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    protected function unserialize($value)
    {
        return $value;
    }
}