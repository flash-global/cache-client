<?php

namespace Fei\Cache;

use ObjectivePHP\Primitives\Collection\Collection;

/**
 * Class FixedFifoCollection
 *
 * @package FlashTrack\Service\Cache
 */
class FifoCollection extends Collection
{
    /**
     * The maximum value the collection can keep at the same time.
     *
     * @var int
     */
    protected $maxItem;

    public function __construct($data = [], int $maxItem = PHP_INT_MAX)
    {
        parent::__construct($data);
        $this->maxItem = $maxItem;
    }

    /**
     * Offset to set
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @throws \ObjectivePHP\Primitives\Exception
     * @since 5.0.0
     */
    public function set($offset, $value)
    {
        if (count($this->getInternalValue()) >= $this->maxItem) {
            array_shift($this->value);
        }

        parent::set($offset, $value);
    }

    /**
     * @param int $maxItem
     *
     * @return Collection
     */
    public function setMaxItem(int $maxItem): Collection
    {
        $this->maxItem = $maxItem;

        return $this;
    }
}
