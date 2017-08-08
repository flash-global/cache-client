<?php

namespace Fei\Cache\Location;

use Doctrine\Common\Collections\ArrayCollection;
use Fei\Service\Locate\Entity\Context;
use Fei\Service\Locate\Entity\Location;
use Fei\Cache\CacheManager as BaseCacheManager;
use Fei\Cache\CacheManagerInterface;
use Fei\Cache\Exception\InvalidCollectionException;
use Fei\Cache\Exception\InvalidKeyException;
use Fei\Cache\Exception\MissingCollectionException;
use Fei\Cache\Exception\MissingKeyException;
use Fei\Cache\FifoCollection;
use ObjectivePHP\Primitives\Collection\Validator\ObjectValidator;

/**
 * Class LocationCacheManager
 * @package Fei\Cache
 */
class CacheManager extends BaseCacheManager
{
    /** Position of the year in the cache key */
    const KEY_DATE_YEAR_START = 0;
    const KEY_DATE_YEAR_LENGTH = 4;

    /** Position of the month in the cache key */
    const KEY_DATE_MONTH_START = 4;
    const KEY_DATE_MONTH_LENGTH = 2;

    /** Position of the day in the cache key */
    const KEY_DATE_DAY_START = 6;
    const KEY_DATE_DAY_LENGTH = 2;

    /** Position of the vehicle ID in the cache key */
    const KEY_ID_START = 14;

    /**
     * Add a FifoCollection to a cache
     *
     * @param string             $key
     * @param FifoCollection $collection
     *
     * @return CacheManagerInterface
     *
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Fei\Cache\Exception\MissingCollectionException
     * @throws \Fei\Cache\Exception\InvalidCollectionException
     * @throws \Fei\Cache\Exception\InvalidKeyException
     * @throws \Fei\Cache\Exception\MissingKeyException
     */
    public function add($key, $collection): CacheManagerInterface
    {
        if (empty($key)) {
            throw new MissingKeyException('A key is mandatory for caching');
        }
        if (!is_string($key) || !$this->validateKey($key)) {
            throw new InvalidKeyException($key);
        }
        if ($collection === null) {
            throw new MissingCollectionException('A Location\Collection is mandatory for caching');
        }
        if (!$collection instanceof Collection) {
            throw new InvalidCollectionException(get_class($collection), Collection::class);
        }

        return parent::add($key, $collection);
    }

    /**
     * Get an item from the cache
     *
     * @param $key
     *
     * @return Collection
     *
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function get($key): Collection
    {
        $item = new Collection();

        if ($this->getCache()->hasItem($key)) {
            // PHP 7 security feature
            $item = unserialize(
                $this->getCache()->getItem($key),
                [
                    'allowed_classes' =>
                        [
                            Collection::class,
                            \ObjectivePHP\Primitives\Collection\Collection::class,
                            ObjectValidator::class,
                            Location::class,
                            ArrayCollection::class,
                            Context::class,
                        ],
                ]
            );
        }

        return $item;
    }

    /**
     * Validate if the key is in the format:
     * Year Month Day Hour Minute Second FleetID
     *
     * example: 1994070700060042
     *
     * @param string $key
     *
     * @return bool
     */
    public function validateKey(string $key): bool
    {
        $id = substr($key, self::KEY_ID_START);

        return
            checkdate(
                (int) substr($key, self::KEY_DATE_MONTH_START, self::KEY_DATE_MONTH_LENGTH),
                (int) substr($key, self::KEY_DATE_DAY_START, self::KEY_DATE_DAY_LENGTH),
                (int) substr($key, self::KEY_DATE_YEAR_START, self::KEY_DATE_YEAR_LENGTH)
            )
            && $id !== false && $id !== ''
            ;
    }

    /**
     * Generate a valid cache key.
     * Final format: 1994070700000042
     *
     * @param \DateTime $date
     * @param string    $id
     *
     * @return string
     */
    public function generateKey(\DateTime $date, string $id): string
    {
        $date->setTime(0, 0);

        return str_replace([' ', ':', '-'], '', $date->format('Y-m-d H:i:s')) . $id;
    }
}
