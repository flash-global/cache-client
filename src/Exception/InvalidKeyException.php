<?php
/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 02/08/2017
 * Time: 18:23
 */

namespace Fei\Cache\Exception;

/**
 * Class InvalidKeyException
 * @package FlashTrack\Service\Cache\Exception
 */
class InvalidKeyException extends \LogicException
{
    public function __construct($key)
    {
        parent::__construct(sprintf('Key "%s" is not valid. Format: "YYYYMMDDHisID"', $key));
    }
}
