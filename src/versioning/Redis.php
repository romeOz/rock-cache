<?php

namespace rock\cache\versioning;

use rock\cache\CacheInterface;

class Redis extends \rock\cache\Redis implements CacheInterface
{
    use VersioningTrait;

    /**
     * @inheritdoc
     */
    public function getTag($tag)
    {
        return $this->storage->get($this->prepareTag($tag));
    }

    /**
     * @inheritdoc
     */
    public function removeTag($tag)
    {
        if (!$this->existsTag($tag)) {
            return false;
        }

        return $this->setInternal($this->prepareTag($tag), microtime(), 0);
    }

    protected function validTimestamp($key, array $tagsByValue = [])
    {
        if (empty($tagsByValue)) {
            return true;
        }
        foreach ($tagsByValue as $tag => $timestamp) {
            if ((!$tagTimestamp = $this->storage->get($tag)) ||
                $this->microtime($tagTimestamp) > $this->microtime($timestamp)
            ) {
                $this->storage->del($key);

                return false;
            }
        }

        return true;
    }
}