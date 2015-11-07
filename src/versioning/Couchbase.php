<?php

namespace rock\cache\versioning;

use rock\cache\CacheInterface;

class Couchbase extends \rock\cache\Couchbase implements CacheInterface
{
    use VersioningTrait;

    /**
     * @inheritdoc
     */
    public function getTag($tag)
    {
        return $this->getInternal($this->prepareTag($tag));
    }

    /**
     * @inheritdoc
     */
    public function removeTag($tag)
    {
        try {
            $this->storage->replace($this->prepareTag($tag), microtime());
        } catch (\CouchbaseException $e) {
            return false;
        }

        return true;
    }

    protected function validTimestamp($key, array $tagsByValue = [])
    {
        if (empty($tagsByValue)) {
            return true;
        }
        $tags = $this->storage->get(array_keys($tagsByValue));
        foreach ($tagsByValue as $tag => $timestamp) {
            if (!isset($tags[$tag]) ||
                (isset($tags[$tag]) && !isset($tags[$tag]->error) && $this->microtime($tags[$tag]->value) > $this->microtime($timestamp))
            ) {
                $this->removeInternal($key);

                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function setTags($key, array $tags = [], &$value = null)
    {
        $value = ['value' => $value, 'tags' => []];
        if (empty($tags)) {
            return;
        }
        $timestamp = microtime();
        $tags = $this->prepareTags($tags);
        $data = $this->storage->get($tags);
        foreach ($tags as $tag) {
            if (isset($data[$tag]) && !isset($data[$tag]->error)) {
                $value['tags'][$tag] = $data[$tag]->value;
                continue;
            }
            $this->setInternal($tag, $timestamp, 0, true);
            $value['tags'][$tag] = $timestamp;
        }
    }
}