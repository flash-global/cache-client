<?php

use Fei\Service\Locate\Entity\Location;
use Fei\Cache\Location\Collection;

/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 02/08/2017
 * Time: 16:10
 */
class LocationCollectionTest extends PHPUnit_Framework_TestCase
{
    /** @var Collection */
    protected $instance;

    public function setUp()
    {
        $this->instance = new Collection();
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

        $this->assertContainsOnlyInstancesOf(Location::class, $this->instance);
    }

    public function testThrowExceptionWhenDontAddALocation()
    {
        $value = 'toto';
        $location = $this->getMockBuilder(Location::class)->getMock();

        $this->instance[] = $location;

        $this->assertAttributeContains($location, 'value', $this->instance);
        $this->assertAttributeCount(1, 'value', $this->instance);

        $this->expectExceptionMessage('New value # (string)  did not pass validation');

        $this->instance[] = $value;
    }

}
