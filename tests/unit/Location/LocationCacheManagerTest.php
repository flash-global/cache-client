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
        $this->instance = new CacheManager(new BlackHole());
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

    public function testCanGetTheAllThePossibleKeyForAVehicle()
    {
        $id = '123';

        $expectedKeys = [
            (new DateTime())->format('Ymd') . '000000' . $id,
            (new DateTime('-1 day'))->format('Ymd') . '000000' . $id,
            (new DateTime('-2 days'))->format('Ymd') . '000000' . $id,
            (new DateTime('-3 days'))->format('Ymd') . '000000' . $id,
            (new DateTime('-4 days'))->format('Ymd') . '000000' . $id
        ];
        $this->instance->setMaxDays(5);
        $keys = $this->instance->getAllKeys($id);

        $this->assertEquals($expectedKeys, $keys);
    }

    public function testCanSetAMaxSearchingLimit()
    {
        $this->instance->setMaxDays(10);

        $this->assertAttributeEquals(10, 'maxDays', $this->instance);
        $this->assertCount(10, $this->instance->getAllKeys('12345'));
    }

    public function testCanRetrieveTheLastLocations()
    {
        $id = '123';
        $locations = new Collection();

        $cache = $this->getMockBuilder(BlackHole::class)->getMock();
        $cache->method('hasItems')
              ->with([
                  (new DateTime())->format('Ymd') . '000000' . $id,
                  (new DateTime('-1 day'))->format('Ymd') . '000000' . $id,
                  (new DateTime('-2 days'))->format('Ymd') . '000000' . $id,
                  (new DateTime('-3 days'))->format('Ymd') . '000000' . $id,
                  (new DateTime('-4 days'))->format('Ymd') . '000000' . $id
              ])
              ->willReturn([
                  (new DateTime('-2 days'))->format('Ymd') . '000000' . $id,
                  (new DateTime('-4 days'))->format('Ymd') . '000000' . $id
              ]);
        $cache->method('hasItem')
              ->with((new DateTime('-2 days'))->format('Ymd') . '000000' . $id)
              ->willReturn(true)
        ;
        $cache->method('getItem')
            ->with((new DateTime('-2 days'))->format('Ymd') . '000000' . $id)
            ->willReturn(serialize($locations))
        ;

        $this->instance->setCache($cache);
        $this->instance->setMaxDays(5);
        $result = $this->instance->retrieveLastLocations($id);

        $this->assertEquals($locations, $result);
    }

    public function testCanRetrieveLocationWithDateInterval()
    {
        $id = '123';
        $from = new DateTime('2017-07-30');
        $to = new DateTime('2017-08-02');

        $collection1 = new Collection();
        $collection2 = new Collection();
        $collection3 = new Collection();

        $cache = $this->getMockBuilder(BlackHole::class)->getMock();
        $cache->method('hasItems')
              ->with([
                  '20170730000000123',
                  '20170731000000123',
                  '20170801000000123',
                  '20170802000000123',
              ])
              ->willReturn([
                  '20170730000000123',
                  '20170731000000123',
                  '20170801000000123'
              ]);
        $cache->method('getItems')
              ->with([
                  '20170730000000123',
                  '20170731000000123',
                  '20170801000000123'
              ])
              ->willReturn([serialize($collection1), serialize($collection2), serialize($collection3)])
        ;

        $this->instance->setCache($cache);
        $result = $this->instance->retrieveLocations($id, $from, $to);

        $this->assertEquals([$collection1, $collection2, $collection3], $result);
    }

    public function testCanRetrieveLastLocations()
    {
        $id = '123';
        $from = new DateTime('-2 days');

        $collection1 = new Collection();
        $collection2 = new Collection();

        $cache = $this->getMockBuilder(BlackHole::class)->getMock();
        $cache->method('hasItems')
              ->with([
                  (new DateTime('-2 days'))->format('Ymd') . '000000' . $id,
                  (new DateTime('-1 day'))->format('Ymd') . '000000' . $id,
                  (new DateTime())->format('Ymd') . '000000' . $id
              ])
              ->willReturn([
                  (new DateTime())->format('Ymd') . '000000' . $id,
                  (new DateTime('-1 day'))->format('Ymd') . '000000' . $id
              ]);
        $cache->method('getItems')
              ->with([
                  (new DateTime())->format('Ymd') . '000000' . $id,
                  (new DateTime('-1 day'))->format('Ymd') . '000000' . $id
              ])
              ->willReturn([serialize($collection1), serialize($collection2)])
        ;

        $this->instance->setCache($cache);
        $result = $this->instance->retrieveLocations($id, $from);

        $this->assertEquals([$collection1, $collection2], $result);
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
