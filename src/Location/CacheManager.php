<?php

namespace Fei\Cache\Location;

use DateInterval;
use DatePeriod;
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

    /** Locations will be saved for this lifetime by default */
    const DEFAULT_CACHE_TTL = 30;

    /**
     * Max number of days we can search back with methods
     * CacheManager::getAllKeys
     * CacheManager::retrieveLastLocations
     *
     * @var int
     */
    protected $maxDays = self::DEFAULT_CACHE_TTL;

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
                    'allowed_classes' => [
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

        return $date->format('YmdHis') . $id;
    }

    /**
     * Return all possible keys for a given ID
     *
     * @param string $id
     *
     * @return array
     */
    public function getAllKeys(string $id): array
    {
        $result = [];
        for ($i = 0; $i < $this->getMaxDays(); $i++) {
            $result[] = $this->generateKey(new \DateTime("- {$i} days"), $id);
        }

        return $result;
    }

    /**
     * Search in the cache for the last location collection of a given ID
     *
     * @param string $id
     *
     * @return Collection
     *
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function retrieveLastLocations(string $id): Collection
    {
        $keys = $this->getCache()->hasItems($this->getAllKeys($id));

        $cleanedDates = array_map(function ($key) use ($id) {
            $date = strstr($key, $id, true);
            return \DateTime::createFromFormat('YmdHis', $date);
        }, $keys);

        $lastLocationKey = $this->generateKey(max($cleanedDates), $id);

        return $this->get($lastLocationKey);
    }

    /**
     * Get the Locations collections of a given ID in an interval of date.
     * If $to is not given, current time of the day will be used.
     *
     * @param string         $id
     * @param \DateTime      $from
     * @param \DateTime|null $to
     *
     * @return Collection[]
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function retrieveLocations(string $id, \DateTime $from, \DateTime $to = null): array
    {
        $to = $to ?? new \DateTime();
        // Date interval exclude the last day, so we have to add one day to stay consistent
        $to->modify('+1 day');

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($from, $interval, $to);

        $keys = [];
        /** @var \DateTime $date */
        foreach ($period as $date) {
            $keys[] = $this->generateKey($date, $id);
        }
        $remainingKeys = $this->getCache()->hasItems($keys);

        return array_map(
            function ($value) {
                return unserialize(
                    $value,
                    [
                    'allowed_classes' => [
                        Collection::class,
                        \ObjectivePHP\Primitives\Collection\Collection::class,
                        ObjectValidator::class,
                        Location::class,
                        ArrayCollection::class,
                        Context::class,
                    ],
                    ]
                );
            },
            $this->getCache()->getItems($remainingKeys)
        );
    }

    /**
     * @param int $maxDays
     *
     * @return CacheManager
     */
    public function setMaxDays(int $maxDays): CacheManager
    {
        $this->maxDays = $maxDays;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxDays(): int
    {
        return $this->maxDays;
    }
}
