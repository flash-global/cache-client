<?php
/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 03/08/2017
 * Time: 10:22
 */

namespace Fei\Cache\Location;

use Fei\Service\Locate\Entity\Location;
use Fei\Cache\FifoCollection;

/**
 * Class LocationCollection
 * @package FlashTrack\Service\Cache\Location
 */
class Collection extends FifoCollection
{
    const MAX_LOCATIONS = 3;

    public function __construct($data = [], $maxItem = self::MAX_LOCATIONS)
    {
        parent::__construct($data, $maxItem);
        $this->restrictTo(Location::class, false);
    }
}
