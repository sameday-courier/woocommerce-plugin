<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;

final class DeletePickupPointRequest implements RequestInterface
{
    /**
     * @var int $samedayId
     */
    private int $samedayId;

    /**
     * @param int $samedayId
     */
    public function __construct(int $samedayId)
    {
        $this->samedayId = $samedayId;
    }

    /**
     * @return int
     */
    public function getSamedayId(): int
    {
        return $this->samedayId;
    }
}
