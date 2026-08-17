<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class DeletePickupPointServiceRequestDto
{
    private int $samedayId;

    public function __construct(int $samedayId)
    {
        $this->samedayId = $samedayId;
    }

    public function getSamedayId(): int
    {
        return $this->samedayId;
    }
}
