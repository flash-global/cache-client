<?php
/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 02/08/2017
 * Time: 19:36
 */

namespace Fei\Cache;

interface CacheManagerInterface
{
    /**
     * Add a value in the cache
     *
     * @param $key
     * @param $value
     *
     * @return CacheManagerInterface
     */
    public function add($key, $value): CacheManagerInterface;

    /**
     * Get an item from the cache
     *
     * @param $key
     *
     * @return mixed|null
     */
    public function get($key);
}
