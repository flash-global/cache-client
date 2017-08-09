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
     * AbstractCacheManager constructor.
     * A CacheManager NEEDS a storage to work with
     *
     * @param StorageInterface $cache
     */
    public function __construct(StorageInterface $cache)
    {
        $this->cache = $cache;
    }


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
