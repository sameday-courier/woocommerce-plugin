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
     * @return array|null such as ['param1', 'param2', 'param3', ...]
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
