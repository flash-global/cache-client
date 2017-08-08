<?php
/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 02/08/2017
 * Time: 18:23
 */

namespace Fei\Cache\Exception;

use ObjectivePHP\Primitives\Collection\Collection;

/**
 * Class InvalidCollectionException
 * @package FlashTrack\Service\Cache\Exception
 */
class InvalidCollectionException extends \LogicException
{
    public function __construct($actual, $expected = Collection::class)
    {
        parent::__construct(
            sprintf(
                'Object shoud be instance of "%s". Instance of "%s" provided.',
                $expected,
                $actual
            )
        );
    }
}
