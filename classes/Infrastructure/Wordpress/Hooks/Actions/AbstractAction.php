<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\ActionInterface;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 1;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [];
    }

    /**
     * @return int
     */
    public function getAcceptedArgs(): int
    {
        return count($this->getParams());
    }
}
