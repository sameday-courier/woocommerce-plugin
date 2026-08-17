<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

use Sameday\Objects\Service\ServiceObject;

final class GetServicesResponseDto
{
    /**
     * @var ServiceObject[]
     */
    private array $services;

    private int $pages;

    /**
     * @param ServiceObject[] $services
     */
    public function __construct(array $services, int $pages)
    {
        $this->services = $services;
        $this->pages = $pages;
    }

    /**
     * @return ServiceObject[]
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
