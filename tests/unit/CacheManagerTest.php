<?php

use Fei\Cache\CacheManager;
use Fei\Cache\FifoCollection;
use Zend\Cache\Storage\Adapter\BlackHole;

/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 02/08/2017
 * Time: 16:10
 */
class CacheManagerTest extends PHPUnit_Framework_TestCase
{
    /** @var CacheManager */
    protected $instance;

    public function setUp()
    {
        $this->instance = new CacheManager();
    }

    public function testCanStore()
    {
        $key = 'toto';
        $value = 'value';

        $cache = $this->getMockBuilder(BlackHole::class)->getMock();
        $cache->expects($this->exactly(1))->method('setItem')->with($key, serialize($value));
        $this->instance->setCache($cache);

        $this->instance->add($key, $value);
    }

    public function testCanRetrieveLocation()
    {
        $key = 'toto';
        $value = new FifoCollection();

        $cache = $this->getMockBuilder(BlackHole::class)->getMock();
        $cache->expects($this->exactly(1))->method('hasItem')->with($key)->willReturn(true);
        $cache->expects($this->exactly(1))->method('getItem')->with($key)->willReturn(serialize($value));
        $this->instance->setCache($cache);

        $result = $this->instance->get($key);
        $this->assertEquals($value, $result);
    }

    public function testReplaceDataWhenAlreadyInCache()
    {
        $key = 'toto';
        $value = 'value';
        $newValue = 'new value';

        $cache = $this->getMockBuilder(BlackHole::class)->getMock();
        $cache->expects($this->exactly(1))->method('setItem')->with($key, serialize($value));
        $cache->expects($this->exactly(1))->method('replaceItem')->with($key, serialize($newValue));
        $cache->expects($this->exactly(2))
              ->method('getItem')
              ->withConsecutive([$key], [$key])
              ->willReturnOnConsecutiveCalls(false, true);

        $this->instance->setCache($cache);

        $this->instance->add($key, $value);
        $this->instance->add($key, $newValue);
    }
}
