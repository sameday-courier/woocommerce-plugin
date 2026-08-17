<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class GetPickupPointsRequestDto
{
    private int $page;

    public function __construct(int $page = 1)
    {
        $this->page = $page;
    }

    public function getPage(): int
    {
        return $this->page;
    }
}
