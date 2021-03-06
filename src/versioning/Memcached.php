<?php
namespace rock\cache\versioning;

use rock\cache\CacheInterface;

class Memcached extends \rock\cache\Memcached implements CacheInterface
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
        return $this->storage->replace($this->prepareTag($tag), microtime(), 0);
    }

    protected function validTimestamp($key, array $tagsByValue = [])
    {
        if (empty($tagsByValue)) {
            return true;
        }
        $tags = $this->storage->getMulti(array_keys($tagsByValue));
        foreach ($tagsByValue as $tag => $timestamp) {
            if (!isset($tags[$tag]) ||
                (isset($tags[$tag]) && $this->microtime($tags[$tag]) > $this->microtime($timestamp))
            ) {
                $this->storage->delete($key);

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
        $data = $this->storage->getMulti($tags);
        foreach ($tags as $tag) {
            if (isset($data[$tag])) {
                $value['tags'][$tag] = $data[$tag];
                continue;
            }
            $this->setInternal($tag, $timestamp, 0);
            $value['tags'][$tag] = $timestamp;
        }
    }
}
