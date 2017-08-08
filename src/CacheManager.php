<?php

namespace Fei\Cache;

/**
 * Class CacheManager
 * @package FlashTrack\Service\Cache
 */
class CacheManager extends AbstractCacheManager
{
    /**
     * Add a value in the cache
     *
     * @param $key
     * @param $value
     *
     * @return CacheManagerInterface
     *
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function add($key, $value): CacheManagerInterface
    {
        if ($success = $this->getCache()->getItem($key, $success)) {
            $this->getCache()->replaceItem($key, serialize($value));
        } else {
            $this->getCache()->setItem($key, serialize($value));
        }

        return $this;
    }

    /**
     * Get an item from the cache
     *
     * @param $key
     *
     * @return mixed|null
     *
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function get($key)
    {
        $item = null;

        if ($this->getCache()->hasItem($key)) {
            $item = unserialize($this->getCache()->getItem($key));
        }

        return $item;
    }
}
