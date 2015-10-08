<?php

namespace rock\cache;


use rock\helpers\Serialize;

trait CacheTrait
{
    use CommonTrait;

    /**
     * Time to live on lock (sec)
     * @var int
     */
    public $lockExpire = 30;

    /**
     * @inheritdoc
     */
    public function removeMulti(array $keys)
    {
        /** @var $this CacheInterface */

        foreach ($keys as $key) {
            $this->remove($key);
        }
    }

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
}