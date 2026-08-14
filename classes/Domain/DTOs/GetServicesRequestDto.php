<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class GetServicesRequestDto
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
