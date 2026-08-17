<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

use SamedayCourier\Shipping\Domain\DTOs\CourierServiceDto;

final class GetServicesResponseDto
{
    /**
     * @var CourierServiceDto[]
     */
    private array $services;

    private int $pages;

    /**
     * @param CourierServiceDto[] $services
     */
    public function __construct(array $services, int $pages)
    {
        $this->services = $services;
        $this->pages = $pages;
    }

    /**
     * @return CourierServiceDto[]
     */
    public function getServices(): array
    {
        return $this->services;
    }

    public function getPages(): int
    {
        return $this->pages;
    }
}
