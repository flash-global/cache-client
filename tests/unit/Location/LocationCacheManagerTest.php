<?php

use Fei\Cache\Exception\InvalidCollectionException;
use Fei\Cache\Exception\InvalidKeyException;
use Fei\Cache\Exception\MissingCollectionException;
use Fei\Cache\Exception\MissingKeyException;
use Fei\Cache\Location\CacheManager;
use Fei\Cache\Location\Collection;
use Zend\Cache\Storage\Adapter\BlackHole;

/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 02/08/2017
 * Time: 16:10
 */
class LocationCacheManagerTest extends PHPUnit_Framework_TestCase
{
    /** @var CacheManager */
    protected $instance;

    public function setUp()
    {
        $this->instance = new CacheManager();
    }

    /**
     * @dataProvider storeLocationProvider
     *
     * @param $key
     * @param $collection
     * @param $exception
     */
    public function testCanStoreALocation($key, $collection, $exception)
    {
        $expectedCall = 1;
        if (!empty($exception)) {
            $this->expectException($exception);
            $expectedCall = 0;
        }

        $cache = $this->getMockBuilder(BlackHole::class)->getMock();
        $cache->expects($this->exactly($expectedCall))->method('setItem')->with($key, serialize($collection));
        $this->instance->setCache($cache);

        $this->instance->add($key, $collection);
    }

    public function testCanRetrieveLocation()
    {
        $key = '20170802000000123';
        $collection = serialize(new Collection());

        $cache = $this->getMockBuilder(BlackHole::class)->getMock();
        $cache->expects($this->exactly(1))->method('hasItem')->with($key)->willReturn(true);
        $cache->expects($this->exactly(1))->method('getItem')->with($key)->willReturn($collection);
        $this->instance->setCache($cache);

        $result = $this->instance->get($key);
        $this->assertEquals(unserialize($collection), $result);
    }

    /**
     * @dataProvider generateValidCacheKeyProvider
     *
     * @param $date
     * @param $id
     * @param $key
     */
    public function testGenerateAValidCacheKey($date, $id, $key)
    {
        $result = $this->instance->generateKey($date, $id);

        $this->assertTrue($this->instance->validateKey($result));
        $this->assertEquals($key, $result);
    }

    public function storeLocationProvider()
    {
        $collection = $this->getMockBuilder(Collection::class)->getMock();

        return [
            'valid' => [
                '20170802000000123', $collection, null
            ],
            'missing key' => [
                null, $collection, MissingKeyException::class
            ],
            'invalid key type' => [
                123456789, $collection, InvalidKeyException::class
            ],
            'invalid key format' => [
                'toto', $collection, InvalidKeyException::class
            ],
            'invalid collection' => [
                '20170802000000123', new StdClass(), InvalidCollectionException::class
            ],
            'missing collection' => [
                '20170802000000123', null, MissingCollectionException::class
            ],
        ];

    }

    public function generateValidCacheKeyProvider()
    {
        return [
            [new DateTime('2017-07-07 08:42:40'), '12345', '2017070700000012345'],
            [new DateTime('1337-01-02 00:00:00'), 'azerty', '13370102000000azerty'],
            [new DateTime('5/25'), '0', '201705250000000'],
            [new DateTime('5/25'), '0', '201705250000000'],
            [new DateTime('July 1st, 2016'), 'null', '20160701000000null'],
        ];

    }
}
