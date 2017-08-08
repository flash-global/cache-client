<?php

use Fei\Locate\Entity\Location;
use Fei\Cache\FifoCollection;

/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 02/08/2017
 * Time: 16:10
 */
class FifoCollectionTest extends PHPUnit_Framework_TestCase
{
    /** @var FifoCollection */
    protected $instance;

    public function setUp()
    {
        $this->instance = new FifoCollection();
    }

    public function testCanAddALocation()
    {
        $location = $this->getMockBuilder(Location::class)->getMock();
        $location2 = $this->getMockBuilder(Location::class)->getMock();

        $this->instance[] = $location;

        $this->assertAttributeContains($location, 'value', $this->instance);
        $this->assertAttributeCount(1, 'value', $this->instance);

        $this->instance[] = $location2;

        $this->assertAttributeContains($location2, 'value', $this->instance);
        $this->assertAttributeCount(2, 'value', $this->instance);

        //TODO: add the possibility to contain only one kind of instance
        $this->assertContainsOnlyInstancesOf(Location::class, $this->instance);
    }

    public function testCanOnlyStoreUpToXLocation()
    {
        $maxLocations = 2;
        $location = $this->getMockBuilder(Location::class)->getMock();
        $location2 = $this->getMockBuilder(Location::class)->getMock();
        $location3 = $this->getMockBuilder(Location::class)->getMock();

        $this->instance->setMaxItem($maxLocations);
        $this->instance[] = $location;
        $this->instance[] = $location2;
        $this->instance[] = $location3;

        $this->assertAttributeCount($maxLocations, 'value', $this->instance);
        $this->assertCount($maxLocations, $this->instance);
    }

    public function testEraseTheOldestEntriesWhenStorageFull()
    {
        $maxLocations = 2;
        $location = $this->getMockBuilder(Location::class)->getMock();
        $location2 = $this->getMockBuilder(Location::class)->getMock();
        $location3 = $this->getMockBuilder(Location::class)->getMock();

        $this->instance->setMaxItem($maxLocations);
        $this->instance[] = $location;
        $this->instance[] = $location2;
        $this->instance[] = $location3;

        $this->assertEquals($location2, $this->instance[0]);
        $this->assertEquals($location3, $this->instance[1]);
    }

    public function testCanDoArrayThings()
    {
        $this->instance['key'] = 'toto';

        $this->assertTrue(isset($this->instance['key']));

        unset($this->instance['key']);

        $this->assertAttributeEmpty('value', $this->instance);
    }
}
