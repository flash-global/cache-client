<?php

namespace Fei\Cache;

use Zend\Cache\Storage\StorageInterface;

/**
 * Class AbstractCacheManager
 * @package FlashTrack\Service\Cache
 */
abstract class AbstractCacheManager implements CacheManagerInterface
{
    /** @var StorageInterface */
    protected $cache;

    /**
     * @param StorageInterface $cache
     *
     * @return CacheManagerInterface
     */
    public function setCache(StorageInterface $cache): CacheManagerInterface
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getCache(): StorageInterface
    {
        return $this->cache;
    }
}
