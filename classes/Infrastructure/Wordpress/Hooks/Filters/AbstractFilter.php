<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\FilterInterface;

abstract class AbstractFilter implements FilterInterface
{
    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 1;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return null;
    }

    /**
     * @return int
     */
    public function getAcceptedArgs(): int
    {
        if (null === $this->getParams()) {
            return 0;
        }

        return count($this->getParams());
    }
}
